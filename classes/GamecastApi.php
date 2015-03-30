<?php

class GamecastApi extends DrupalApi {

	function getGamecast($match_nid) {

		if((int)$match_nid <= 0 ){
			return 'Invalid Data';
		}
		$return = array();
		$result = db_select('field_data_field_gamecast_events','evts');

		$result->innerJoin('field_data_field_gamecast_match', 'm', 'evts.entity_id = m.entity_id');

		$result->leftJoin('field_data_field_gamecast_event_minute', 'minute', 'minute.entity_id = evts.field_gamecast_events_value');
		$result->leftJoin('field_data_field_gamecast_event_type', 'type', 'type.entity_id = evts.field_gamecast_events_value');
		$result->leftJoin('field_data_field_gamecast_event_text', 'text', 'text.entity_id = evts.field_gamecast_events_value');
		$result->leftJoin('field_data_field_gamecast_event_photo', 'photo', 'photo.entity_id = evts.field_gamecast_events_value');
		$result->leftJoin('field_data_field_gamecast_event_video', 'video', 'video.entity_id = evts.field_gamecast_events_value');

		$result->leftJoin('taxonomy_term_data', 'term', 'term.tid = type.field_gamecast_event_type_tid');
		$result->leftJoin('field_data_field_match_event_type_code', 'type_term', 'term.tid = type_term.entity_id');

		$result->leftJoin('file_managed', 'file_photo', 'file_photo.fid = photo.field_gamecast_event_photo_fid');
		$result->leftJoin('file_managed', 'file_video', 'file_video.fid = video.field_gamecast_event_video_fid');

		$result = $result->fields('evts', array('field_gamecast_events_value'));
		$result = $result->fields('minute', array('field_gamecast_event_minute_value'));
		//$result = $result->fields('term', array('field_gamecast_event_minute_value'));
		$result = $result->fields('text', array('field_gamecast_event_text_value'));
		$result = $result->fields('file_photo', array('uri'));
		$result = $result->fields('file_video', array('uri'));

		$result = $result->fields('type_term', array('field_match_event_type_code_value'));

		$result = $result->condition('m.field_gamecast_match_nid', $match_nid)
			->orderBy('minute.field_gamecast_event_minute_value', 'DESC')
			->execute()
			->fetchAllAssoc('field_gamecast_events_value');

		if(!empty($result)) {
			foreach($result as $_row) {
				$return[] = $_row; /* array(
					'cid' => $_row->cid,
					'uid' => $_row->uid,
					'username' => $_row->name,
					'comment' => $_row->comment_body_value,
					'created' => date('c', $_row->created)

				);*/
			}
		}
		return $this->jsonResonse($return);
	}

}