<?php


class CommentsApi extends DrupalApi {

	function getNodeComments($nid, $page = 1) {
		$itemsPerPage = 25;
		if((int)$nid <= 0 || (int)$page <= 0){
			return 'Invalid Data';
		}

		$count = db_select('comment','c')
			->condition('c.status', 1)
			->condition('c.nid', $nid);
		$count->addExpression('COUNT(*)');
		$count =$count->execute()
			->fetchField();
		$return = array();

		if($count > 0) {
			$result = db_select('comment','c')
				->fields('c', array('cid', 'uid', 'created'));
			$result->innerJoin('field_data_comment_body', 'cb', 'cb.entity_id = c.cid');
			$result->innerJoin('users', 'u', 'u.uid = c.uid');
			$result = $result->fields('cb', array('comment_body_value'));
			$result = $result->fields('u', array('name'))
				->condition('c.status', 1)
				->condition('c.nid', $nid)
				->orderBy('c.created', 'DESC')
				->range(($page - 1) * $itemsPerPage, $itemsPerPage)
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
		}
		$return = array(
			'total' => (int)$count,
			'pages' => ceil($count / $itemsPerPage),
			'comments' => $return
		);

		return $this->jsonResonse($return);
	}


	function postComment($nid, $app) {
		$request = $app->request;
		$comment = trim($request->post('comment'));

		if(empty($comment) || empty($app->user) && $app->user->uid == 0) {
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
				'uid' => $app->user->uid,
				'pid' => 0,
				'nid' => $nid,
				'subject' => mb_substr($comment, 10, 'UTF-8'),
				'hostname' => $request->getIp(),
				'created' => time(),
				'changed' => time(),
				'status' => 1,
				'thread' => $thread,
				'name' => $app->user->name,
				'mail' => '',
				'homepage' => '',
				'language' => LANGUAGE_NONE,
				'uuid' => $app->user->uuid
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
			return $this->getNodeComments($nid);
		}
		else
			return null;
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
