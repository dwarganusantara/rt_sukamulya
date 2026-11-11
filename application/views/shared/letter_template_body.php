<?php
$lines=preg_split('/\R/u',trim((string)$renderedTemplate));
$inTable=false;
foreach($lines as $line):
    $line=trim($line);
    if($line===''):
        if($inTable): ?></tbody></table><?php $inTable=false; endif;
        continue;
    endif;
    if(preg_match('/^([^:]{2,45})\s*:\s*(.*)$/u',$line,$match)):
        if(!$inTable): ?><table class="letter-fields"><tbody><?php $inTable=true; endif; ?>
        <tr><td><?= html_escape(trim($match[1])) ?></td><td>:</td><td><?= html_escape(trim($match[2])) ?></td></tr>
    <?php else:
        if($inTable): ?></tbody></table><?php $inTable=false; endif; ?>
        <p><?= html_escape($line) ?></p>
    <?php endif;
endforeach;
if($inTable): ?></tbody></table><?php endif; ?>
