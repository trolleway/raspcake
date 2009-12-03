<?php
    class Extline extends AppModel {
    	

		
		
 function get_lines_pages_url()
    {

        $urls[]=array('line_code'=>'msk_len', 'url'=>'http://rasp.yandex.ru/city/213/direction?direction=msk_len');
        $urls[]=array('line_code'=>'msk_riz', 'url'=>'http://rasp.yandex.ru/city/213/direction?direction=msk_riz');
        $urls[]=array('line_code'=>'msk_kur', 'url'=>'http://rasp.yandex.ru/city/213/direction?direction=msk_kur');
        $urls[]=array('line_code'=>'msk_kiv', 'url'=>'http://rasp.yandex.ru/city/213/direction?direction=msk_kiv');
        $urls[]=array('line_code'=>'msk_pav', 'url'=>'http://rasp.yandex.ru/city/213/direction?direction=msk_pav');
        #$urls[]=array('line_code'=>'msk_len', 'url'=>'savepages/page_from_inet.htm');

        
        return $urls;
    }
	
		
	}
?>