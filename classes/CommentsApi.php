<?php


class CommentsApi extends DrupalApi {

	function getNodeComments($nid, $page = 1) {

		if((int)$nid <= 0 || (int)$page <= 0){
			return 'Invalid Data';
		}
		$return = array();
		$result = db_select('comment','c')
			->fields('c', array('cid', 'uid', 'created'));
		$result->innerJoin('field_data_comment_body', 'cb', 'cb.entity_id = c.cid');
		$result->innerJoin('users', 'u', 'u.uid = c.uid');
		$result = $result->fields('cb', array('comment_body_value'));
		$result = $result->fields('u', array('name'))
			->condition('c.status', 1)
			->condition('c.nid', $nid)
			->orderBy('c.created', 'DESC')
			->range(($page - 1) * 25, $page * 25)
			->execute()
			->fetchAllAssoc('cid');
		if(!empty($result)) {
			foreach($result as $_row) {
				$return[] = array(
					'cid' => $_row->cid,
					'uid' => $_row->uid,
					'username' => $_row->name,
					'comment' => $_row->comment_body_value,
					'created' => date('c', $_row->created)

				);
			}
		}
		return $this->jsonResonse($return);
	}


	function postComment($nid, $request) {

		$uid = $request->post('uid');
		$uuid = $request->post('uuid');



		$comment = strip_tags($request->post('comment'));

		if(empty($uid) || empty($comment) || empty($uuid) || ($username = $this->getUser($uid, $uuid)) == null) {
			return null;
		}

		$max = db_query('SELECT MAX(thread) FROM comment WHERE nid = :nid', array(':nid' => $nid))->fetchField();
		// Strip the "/" from the end of the thread.
		$max = rtrim($max, '/');
		$parts = explode('.', $max);
		$firstsegment = $parts[0];
		$thread = $this->int2vancode($this->vancode2int($firstsegment) + 1) . '/';

		$cid = db_insert('comment')
			->fields(array(
				'uid' => $uid,
				'pid' => 0,
				'nid' => $nid,
				'subject' => substr($comment, 10),
				'hostname' => $request->getIp(),
				'created' => time(),
				'changed' => time(),
				'status' => 1,
				'thread' => $thread,
				'name' => $username,
				'mail' => '',
				'homepage' => '',
				'language' => LANGUAGE_NONE,
				'uuid' => $uuid
			))
			->execute();
		$res = null;
		if($cid > 0) {

			$node_type = db_select('node', 'n')->fields('n',array('type'))->condition('n.nid', $nid)->execute()->fetchField();
			if($node_type){
				$res = db_insert('field_data_comment_body')
					->fields(array(
						'entity_type' => 'comment',
						'bundle' => 'comment_node_'.$node_type,
						'deleted' => '0',
						'entity_id' => $cid,
						'revision_id' => $cid,
						'language' => LANGUAGE_NONE,
						'delta' => 0,
						'comment_body_value' => $comment,
						'comment_body_format' => NULL,

					))
					->execute();
			}
		}

		if($res !== null) {
			$this->clearCache('comments_'.$nid);
			return $this->getNodeComments($nid);
		} else return null;
	}

	private function int2vancode($i = 0) {
		$num = base_convert((int) $i, 10, 36);
		$length = strlen($num);

		return chr($length + ord('0') - 1) . $num;
	}

	/**
	 * Decode vancode back to an integer.
	 */
	private function vancode2int($c = '00') {
		return base_convert(substr($c, 1), 36, 10);
	}


}
