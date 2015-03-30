<?php


class NewsApi extends DrupalApi {

	function get($page = 1) {

		if((int)$page <= 0){
			return 'Invalid Data';
		}
		$return = array();
		$result = db_select('node','n')
						->fields('n', array('nid', 'title', 'created'));
		$result->innerJoin('field_data_body', 'fdb', 'fdb.entity_id = n.nid');
		$result->innerJoin('field_data_field_news_image', 'img', 'img.entity_id = n.nid');
		$result->innerJoin('file_managed', 'file', 'file.fid = img.field_news_image_fid');
		$result = $result->fields('fdb', array('body_summary'));
		$result = $result->fields('file', array('uri'))
						->condition('n.status', 1)
						->condition('n.type', 'news')
						->orderBy('n.created', 'DESC')
						->range(($page - 1) * 25, $page * 25)
						->execute()
						->fetchAllAssoc('nid');
		if(!empty($result)) {
			foreach($result as $_row) {
				$return[] = array(
					'nid' => $_row->nid,
					'title' => $_row->title,
					'teaser' => $_row->body_summary,
					'image' =>  BASE_URL.'/'.variable_get('file_public_path','').'/'.file_uri_target($_row->uri),
					'created' => date('c', $_row->created)

				);
			}
		}
		return $this->jsonResonse($return);
	}
	function getMatchNews($nid, $page = 1) {

                if((int)$nid <= 0 || (int)$page <= 0){
                        return 'Invalid Data';
                }
                $return = array();
                $result = db_select('node','n')
                	 ->fields('n', array('nid', 'title', 'created'));
                $result->innerJoin('field_data_body', 'fdb', 'fdb.entity_id = n.nid');
                $result->innerJoin('field_data_field_news_image', 'img', 'img.entity_id = n.nid');
                $result->innerJoin('file_managed', 'file', 'file.fid = img.field_news_image_fid');
		$result->innerJoin('field_data_field_news_match', 'nm', 'nm.entity_id = n.nid');
                $result = $result->fields('fdb', array('body_summary'));
                $result = $result->fields('file', array('uri'))
                                                ->condition('n.status', 1)
                                                ->condition('n.type', 'news')
						->condition('nm.field_news_match_nid', $nid)
                                                ->orderBy('n.created', 'DESC')
                                                ->range(($page - 1) * 25, $page * 25)
                                                ->execute()
                                                ->fetchAllAssoc('nid');
                if(!empty($result)) {
                        foreach($result as $_row) {
                                $return[] = array(
                                        'nid' => $_row->nid,
                                        'title' => $_row->title,
                                        'teaser' => $_row->body_summary,
                                        'image' =>  BASE_URL.'/'.variable_get('file_public_path','').'/'.file_uri_target($_row->uri),
                                        'created' => date('c', $_row->created)

                                );
                        }
                }
		return $this->jsonResonse($return);
        }



}
