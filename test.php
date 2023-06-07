<?php


//include DIR.'/utility/meta_contact.php';
//exit;



//$db->debugGlobal=true;
////$select_time = $db->build()->select('sum(time)')->from('table')->where(['table.clinic_id', '~clinic.id', '='])->run();
//
//show($db->build()->from('user')->count());



//
//show($db->build()->select('id, name')
//    ->order('id', 'ASC')->from('user')
//    ->offset(99)
//    ->sub($select_time, 'sum_clinic')
//    ->select('test, text2, text3', 'a')
//    ->leftJoin('clinic as c', ['c.id' => 'u.clinic_id'])
//    ->leftJoin('clinic as c', ['c.id' => 'u.clinic_id'])
//    ->having(['sum(price)', 12, '>'])
//    //->where([['sum(price)', 12, '>']])
//       ->where('test > 12', 'AND', 'AND')
//   ->where(['username', 'guest', 'REGEXP'])
////    ->where([1,3,4,5], 'AND', 'AND', 1)
//////    ->where([1,2,3,4,5])
//////    ->where(['etst', 'fdsfsdfd'])
////    ->where([
////        ['name', '333', 'LIKE'],
////        ['age', 85],
////        $db::where_group(['username' => 'admin2', 'password'=> 'test2'])
////    ], 'OR', 'OR', 1)
////    ->where(['username' => 'admin', 'password'=> 'test'], 'OR', 'AND', true)
//    //->show()
//    ->run(4, 1));
//exit;

//
//
//$rows = $db->sel('*', 'client_meta', ['name' => ['LIKE', 'phone%']]);
//$res = [];
//foreach($rows as $row) {
//    $db->upd('client_meta', ['value' => format_phone_set($row['value'])], ['name' => $row['name'], 'client_id' => $row['client_id']]);
//}
//
//foreach($res as $id=>$v){
//    if(!isset($v['phone_0'])) continue;
//   if(strpos($v['phone_0'], ',') !== FALSE){
//      // show($v);
////       $phones = explode(',', $v['phone_0']);
////       for($i=0; sizeof($phones)>0; $i++){
////           $phone = array_shift($phones);
////           $db->upd('client_meta', ['value' => $phone], ['name' => 'phone_'.$i, 'client_id'=>$id]);
////       }
//   }
//}
//
//
//
//exit;
//load_models('parses/Parse_clients');
//new Parse_clients(DIR.'/uploads/patients/1/patients.csv');
//exit;
