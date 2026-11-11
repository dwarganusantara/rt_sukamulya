<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Resident popup: decisions made by admin on the resident's own submissions. */
class Keputusan extends CI_Controller
{
    private function citizen()
    {
        $id = (int) $this->session->userdata('citizen_id');
        $this->output->set_header('Cache-Control: no-store, private')->set_content_type('application/json');
        if (!$id) { $this->output->set_status_header(401)->set_output('{"items":[]}'); return 0; }
        $this->load->helper('citizen_notice');
        return $id;
    }

    /** GET warga/keputusan — polled by assets/citizen-decision.js */
    public function index()
    {
        if (!$id = $this->citizen()) return;
        $this->output->set_output(json_encode(array('items' => citizen_notice_items($id)), JSON_UNESCAPED_UNICODE));
    }

    /**
     * POST warga/keputusan/baca — mark shown decisions as seen.
     * Excluded from CSRF (config) so this background call does not rotate the
     * token used by forms on the page; it can only flag the caller's own notices.
     */
    public function baca()
    {
        if (!$id = $this->citizen()) return;
        if ($this->input->method(TRUE) !== 'POST') { $this->output->set_status_header(405)->set_output('{}'); return; }
        $items = json_decode((string) $this->input->raw_input_stream, TRUE);
        $this->output->set_output(json_encode(array('saved' => citizen_notice_mark_seen($id, $items['items'] ?? array()))));
    }
}
