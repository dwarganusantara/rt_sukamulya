param([string]$OutputRoot='C:\xampp\htdocs\sukamulya\docs\dashboard-qa')
$ErrorActionPreference='Stop'
$qa=Join-Path $env:TEMP 'sukamulya-dashboard-qa'
New-Item -ItemType Directory -Force -Path $OutputRoot | Out-Null
$chrome='C:\Program Files\Google\Chrome\Application\chrome.exe'
$browserProcess=Start-Process -FilePath $chrome -ArgumentList @('--headless','--disable-gpu','--no-first-run','--no-default-browser-check','--allow-file-access-from-files','--remote-debugging-port=9237',('--user-data-dir='+$qa+'\chrome-cdp')) -WindowStyle Hidden -PassThru
$ws=$null;$script:seq=0
function Cdp([string]$method,$params=@{}){
 $script:seq++;$requestId=$script:seq
 $bytes=[Text.Encoding]::UTF8.GetBytes((@{id=$requestId;method=$method;params=$params}|ConvertTo-Json -Depth 25 -Compress))
 $ws.SendAsync([ArraySegment[byte]]::new($bytes),[Net.WebSockets.WebSocketMessageType]::Text,$true,[Threading.CancellationToken]::None).GetAwaiter().GetResult()|Out-Null
 while($true){$stream=[IO.MemoryStream]::new();do{$buffer=New-Object byte[] 1048576;$received=$ws.ReceiveAsync([ArraySegment[byte]]::new($buffer),[Threading.CancellationToken]::None).GetAwaiter().GetResult();$stream.Write($buffer,0,$received.Count)}while(-not $received.EndOfMessage);$msg=[Text.Encoding]::UTF8.GetString($stream.ToArray())|ConvertFrom-Json;$stream.Dispose();if($msg.id -eq $requestId){if($msg.error){throw ($msg.error|ConvertTo-Json)};return $msg.result}}
}
try{
 $target=$null;for($i=0;$i -lt 20;$i++){try{$targets=Invoke-RestMethod 'http://localhost:9237/json';$target=$targets|Where-Object type -eq 'page'|Select-Object -First 1;if($target){break}}catch{};Start-Sleep -Milliseconds 300}
 if(-not $target){throw 'Chrome target unavailable'}
 $ws=[Net.WebSockets.ClientWebSocket]::new();$ws.ConnectAsync([Uri]$target.webSocketDebuggerUrl,[Threading.CancellationToken]::None).GetAwaiter().GetResult()|Out-Null
 Cdp 'Page.enable'|Out-Null
 foreach($size in @(320,375,390,768,1440)){
  Cdp 'Emulation.setDeviceMetricsOverride' @{width=$size;height=1000;deviceScaleFactor=1;mobile=($size -lt 700)}|Out-Null
  Cdp 'Page.navigate' @{url=('file:///'+$qa.Replace('\','/')+'/dashboard.html')}|Out-Null
  Cdp 'Runtime.evaluate' @{expression='new Promise(resolve=>setTimeout(()=>document.fonts.ready.then(resolve),1200))';awaitPromise=$true}|Out-Null
  $metrics=Cdp 'Runtime.evaluate' @{expression='JSON.stringify({width:innerWidth,scroll:document.documentElement.scrollWidth,columns:getComputedStyle(document.querySelector(".dwi-page > .dwi-grid")).gridTemplateColumns.split(" ").length,tiles:document.querySelectorAll(".dwi-page > .dwi-grid .dwi-tile").length})';returnByValue=$true}
  Write-Output ('Dashboard '+$size+': '+$metrics.result.value)
  $m=$metrics.result.value|ConvertFrom-Json;if($m.scroll -gt $m.width){throw ('Horizontal overflow at '+$size)}
  if($size -eq 390 -or $size -eq 1440){
   $checks=Cdp 'Runtime.evaluate' @{expression=[IO.File]::ReadAllText('C:\xampp\htdocs\sukamulya\tests\dashboard_favorites_checks.js');returnByValue=$true}
   if($checks.exceptionDetails){throw ($checks.exceptionDetails|ConvertTo-Json -Depth 5)}
   Write-Output ('Favorite interactions '+$size+': '+$checks.result.value)
   $outcomes=$checks.result.value|ConvertFrom-Json;foreach($prop in $outcomes.PSObject.Properties){if(-not $prop.Value){throw ('Favorite interaction failed: '+$prop.Name)}}
  }
  $capture=Cdp 'Page.captureScreenshot' @{format='png';captureBeyondViewport=$true};[IO.File]::WriteAllBytes((Join-Path $OutputRoot ('dashboard-'+$size+'.png')),[Convert]::FromBase64String($capture.data))
 }
 foreach($size in @(1440,390)){
  Cdp 'Emulation.setDeviceMetricsOverride' @{width=$size;height=1100;deviceScaleFactor=1;mobile=($size -lt 700)}|Out-Null
  Cdp 'Page.navigate' @{url=('file:///'+$qa.Replace('\','/')+'/admin.html')}|Out-Null
  Cdp 'Runtime.evaluate' @{expression='new Promise(resolve=>setTimeout(()=>document.fonts.ready.then(resolve),1400))';awaitPromise=$true}|Out-Null
  $result=Cdp 'Runtime.evaluate' @{expression='JSON.stringify({width:innerWidth,scroll:document.documentElement.scrollWidth,editor:!!document.querySelector("#dashboard-settings-form"),preview:document.querySelectorAll("#dws-preview .dwi-tile").length})';returnByValue=$true};Write-Output ('Admin '+$size+': '+$result.result.value)
  $capture=Cdp 'Page.captureScreenshot' @{format='png';captureBeyondViewport=$true};[IO.File]::WriteAllBytes((Join-Path $OutputRoot ('admin-'+$size+'.png')),[Convert]::FromBase64String($capture.data))
  $capture=Cdp 'Page.captureScreenshot' @{format='png';captureBeyondViewport=$false};[IO.File]::WriteAllBytes((Join-Path $OutputRoot ('admin-top-'+$size+'.png')),[Convert]::FromBase64String($capture.data))
  $checks=Cdp 'Runtime.evaluate' @{expression=[IO.File]::ReadAllText('C:\xampp\htdocs\sukamulya\tests\dashboard_preview_checks.js');returnByValue=$true}
  if($checks.exceptionDetails){throw ($checks.exceptionDetails|ConvertTo-Json -Depth 5)}
  Write-Output ('Editor interactions '+$size+': '+$checks.result.value)
  $outcomes=$checks.result.value|ConvertFrom-Json;foreach($prop in $outcomes.PSObject.Properties){if(-not $prop.Value){throw ('Editor interaction failed: '+$prop.Name)}}
  Cdp 'Runtime.evaluate' @{expression='document.querySelector(".dws-preview-section").scrollIntoView()'}|Out-Null
  $capture=Cdp 'Page.captureScreenshot' @{format='png';captureBeyondViewport=$false};[IO.File]::WriteAllBytes((Join-Path $OutputRoot ('admin-preview-'+$size+'.png')),[Convert]::FromBase64String($capture.data))
 }
}finally{if($ws){$ws.Dispose()};if($browserProcess -and -not $browserProcess.HasExited){Stop-Process -Id $browserProcess.Id -ErrorAction SilentlyContinue}}