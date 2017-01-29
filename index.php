<form method="POST">
  <p><input type="text" placeholder="Ограничитель паст страничной навигации" name="post_max"></p>
  <p><input type="text" placeholder="Ведите ссылку на категорию " name="url"></p>
  <p><input type="submit"></p>
 </form>
<?php

//header('Content-type: text/html; charset=utf-8');
ini_set("max_execution_time", 0);
require_once 'simple_html_dom.php'; 
require_once 'phpQuery.php'; 


$post_max=$_POST['post_max'];
$url=$_POST['url'];



parser($url,$post_max,$post_tek=1);//запускаем функцию 

function parser($url,$post_max,$post_tek){
   
    @$file=file_get_contents($url);
     $doc=phpQuery::newDocument($file);
     //$post_tek=0;
     foreach ($doc->find('.b-product-gallery .b-online-edit') as $article) {
       $article=pq($article);
 
        $link_tovar=$article->find('.b-product-gallery__title')->attr('href');

          $f = fopen("file.txt", "a+");
            fwrite($f, $link_tovar."\r\n"); 
     }
     
    //проходим по постраничной навигации
    $next=$doc->find('.b-pager__link_type_current')->next()->attr('href');
    $url_post= "http://shock.org.ua".$next;
    //$aa=$url.$next;
    
    if ($post_tek<$post_max){ 

       if ($url != $url_post ){
           $post_tek++; 
          parser($url_post,$post_max,$post_tek);
          
        }
     
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$massfiletxt = file('file.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); //переобразуем текстовый файл в массив
$arr=array();
$data=array();

foreach ($massfiletxt as $key => $url) {
  $arr[]=parserfoods($url,$min=0);
 //echo $url;
}


$data=array();//создаем массив с товарами 

 //print_r(parserfoods($url,$min=0));
function parserfoods($url,$min){
  
     //создаем обьект 
     $file=file_get_contents($url);
     $doc=phpQuery::newDocument($file);
     $article=pq($doc->find('.b-container .b-page__row'));
     $article=pq($article);
      
    //собираем данные
    $title=$article->find('.b-title')->text(); //название 
    $nalichie= pq($doc->find('.b-product__info-holder'))->find('.b-product-data__item_type_available')->text();//наличие 
    if ($nalichie=="В наличии"){
      $nalichie=998;
    }
    else{
      $nalichie=0;
    }
 
    $art= preg_replace('/Код: /','',$article->find('.b-product-data__item_type_sku:visible:has(span)')->text()); //артикул
    $price= preg_replace('/грн./','',$article->find('.b-product-cost__price:visible:has(span)')->text()); //цена b-user-content 

    $img_url=preg_replace('/200/','640',$article->find('img')->attr('src'));  //картинку
    $img_name= preg_replace('/http:\/\/images.ua.prom.st/', '', $img_url);
    @file_put_contents('foto/'.$img_name,file_get_contents($img_url));
   
  
    $opisanie1= pq($doc->find('.b-page__row'))->find('div[data-qaid="product_description"]')->html(); //описание 
    $opisanie = preg_replace ("!<a.*?href=\"?'?http:\/\/([^ \"'>]+)\"?'?.*?>(.*?)</a>!is", "\\2", $opisanie1); //убираем активные ссылки из описания
    //$haractaristiki= pq($doc->find('.b-page__row'))->find('.b-online-edit:visible:has(table)')->html(); //описание 
    $brend= pq($doc->find('.b-page__row'))->find('.b-product-info tr:eq(1) td:eq(1)')->html(); //бреннд

      
     $data=array($title,$nalichie,$art,$art."- yavshoke",(int)$price,$opisanie,$brend,'data/yavshoke'.$img_name); //записуем в массив парсинг 
                
    $min++;
  //если нам пришел пустой массив то пропускаем еще раз не более 3-х раз
    if ($data === Array() and $min<4){
       parserfoods($url,$min);
    }
    else {
      if ($data == Array()){
         $data[]='';
       return $data;
      }
      else{
        return $data;
      }
      
    }
    //$data->clear();
  }//конец функции 


$title=array('Name','Nalichie','Model','Sku','price','Description','Manufacturer','foto','cat1','cat2','cat3'); //записуем в массив парсинг 


//готовый результат записуем в файл
@$df = fopen("file/result_".time().".csv", 'a+');
@fwrite($df,b"\xEF\xBB\xBF" ) ;
  fputcsv($df, $title, ';');//создаем шапку 
    foreach ($arr as $row) {
        fputcsv($df, $row, ';'); //записуем данные в таблицу
    }
 fopen("file.txt", "w+");   //обнуляем тектовый файл

?>