<?php

if (!defined('IN_IA')) {

	exit('Access Denied');
}



class Category_ChingLeeingPage extends ComWebPage
{
	public function __construct($_com = 'coupon')
	{
		parent::__construct($_com);
	}

	public function main()
	{
		global $_W;
		global $_GPC;


		if (!empty($_GPC['catid'])) {

			foreach ($_GPC['catid'] as $k => $v ) {

				$data = array('name' => trim($_GPC['catname'][$k]), 'displayorder' => $k, 'status' => intval($_GPC['status'][$k]), 'uniacid' => $_W['uniacid']);


				if (empty($v)) {

					pdo_insert('ching_leeing_coupon_category', $data);
					$insert_id = pdo_insertid();
				}

				 else {

					pdo_update('ching_leeing_coupon_category', $data, array('id' => $v));
				}

			}
            show_json(1);
		}





		$list = pdo_fetchall('SELECT * FROM ' . tablename('ching_leeing_coupon_category') . ' WHERE uniacid = \'' . $_W['uniacid'] . '\' and merchid=0 ORDER BY displayorder asc');
		include $this->template();
	}

	public function delete()
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);
		$item = pdo_fetch('SELECT id,name FROM ' . tablename('ching_leeing_coupon_category') . ' WHERE id = \'' . $id . '\' and merchid=0 AND uniacid=' . $_W['uniacid'] . '');


		if (!empty($item)) {

			pdo_delete('ching_leeing_coupon_category', array('id' => $id));
		}





		show_json(1);
	}
}


?>