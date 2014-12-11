<?php
ini_set("memory_limit","48M");
$file=fopen('input_file', 'r'); // !specify the input file here
$lines="";
$jegyek=array();

// read input file contents into variable
  while (!feof($file)){
    $single_line=fgets($file);
    //print $single_line."<br/>";
    $lines.=trim($single_line)."\n";
  }
$file_contents=explode("\n\n\n", $lines);

foreach ($file_contents as $k => $e){
  $word_form=substr($e, strpos($e, "{")+1, (strpos($e, "}")-strpos($e, "{"))-1);


/*
** remove '+Token' feature
*/

  //     ... |"+Token"}
  if (strpos($e, "|\"+Token\"}")!==false){
    //echo $e."<br />";
    $supl=substr($e, strpos($e, "|\"+Token\"}")+9);
    $e=substr($e, 0, strpos($e, "|\"+Token\"}")).$supl;
    //echo $e."<br /><br /><br />";
  }

  //    ... |word_form "+Token"}
  if (strpos($e, "|".$word_form." \"+Token\"}")!==false){
    //echo $e."<br />";
    $supl=substr($e, strpos($e, "|".$word_form." \"+Token\"}")+strlen($word_form)+10);
    $e=substr($e, 0, strpos($e, "|".$word_form." \"+Token\"}")).$supl;
    //echo $e."<br /><br /><br />";
  }

  //     ... |"+Token"| ...
  if (strpos($e, "|\"+Token\"|")!==false){
    //echo $e."<br />";
    $supl=substr($e, strpos($e, "|\"+Token\"|")+9);
    $e=substr($e, 0, strpos($e, "|\"+Token\"|")).$supl;
    //echo $e."<br /><br /><br />";
  }

  //     {"+Token"| ...
  if (strpos($e, "{\"+Token\"|")!==false){
    //echo $e."<br />";
    $supl=substr($e, strpos($e, "{\"+Token\"|")+10);
    $e=substr($e, 0, strpos($e, "{\"+Token\"|")+1).$supl;
    //echo $e."<br /><br /><br />";
  }

  //     ... word_form "+Token"| ...
  if (strpos($e, $word_form." \"+Token\"|")!==false){
    //echo $e."<br />";
    $supl=substr($e, strpos($e, $word_form." \"+Token\"|")+strlen($word_form)+10);
    $e=substr($e, 0, strpos($e, $word_form." \"+Token\"|")).$supl;
    //echo $e."<br /><br /><br />";
  }

  //     ... word_form "+Token" \n | ...
  if (strpos($e, $word_form." \"+Token\"\n|")){
    //echo $e."<br />";
    $supl=substr($e, strpos($e, $word_form." \"+Token\"\n|")+strlen($word_form)+11);
    $e=substr($e, 0, strpos($e, $word_form." \"+Token\"\n|")).$supl;
    //echo $e."<br /><br /><br />";
  }

  //     ... "+Token" \n | ...
  if (strpos($e, "\"+Token\"\n|")){
    //echo $e."<br />";
    $supl=substr($e, strpos($e, "\"+Token\"\n|")+10);
    $e=substr($e, 0, strpos($e, "\"+Token\"\n|")).$supl;
    //echo $e."<br /><br /><br />";
  }

  //     ... "+Token" | ...     ( {pl} )
  if (strpos($e, " \"+Token\"|")){
    //echo $e."<br />";
    $supl=substr($e, strpos($e, " \"+Token\"|")+10);
    $e=substr($e, 0, strpos($e, " \"+Token\"|")).$supl;
    //echo $e."<br /><br /><br />";
  }

  if (strpos($e, "\"+Token\"")){
    continue;
  }

/*
** take analyses level by level, based on {, } chars
*/

  //print $e."<br><br>";
  $x=0; $dep=0; $pos=0;
  for ($i=0; $e[$i]; ++$i){
    //print $e[$i];
    if ($e[$i]=="{")
      ++$x;
    if ($dep<$x){
      $dep=$x;
      $pos=$i;
    }
    if ($e[$i]=="}")
      --$x;
  }
//  print $x."<br><br><br>";
  $pos2=strpos($e, '}', $pos);

  $e=substr($e, strpos($e, "analyzing {".$word_form."}\n\n")+14+strlen($word_form));
//  print "<br><br>".$e;
//  break;
  $fset=array();
  for ($a=0; $a<=$dep; ++$a){
    $c=0;  $status=0;  $nov=0;  //print "<br><br>a: ".$a;
    $slot=0;
    for ($b=0; $e[$b]; ++$b) {

      if ($e[$b]=='{'){  //print "<br>IN ".($c+1);
         ++$c; $nov=1;
      }

      if ($c==$a+2 && $status==0 && $nov==1){ //print "<br>a:&nbsp;".$a."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;c:&nbsp;".$c;
          $temp=$temp."&nbsp;#SLOT".$slot++."#&nbsp;";
          $status=1;
      }

      if ($c==$a+1 ){
          $temp=$temp.$e[$b];
      }
      if ($e[$b]=='}'){  //print "<br>OUT ".($c-1);
        --$c;
        $status=0; $nov=0;
      }

    }

    //print "<br>".$temp;
    if ($temp!="")
        $fset[].=$temp;
    $temp="";
  
  }


/*
** analyses into array
*/

  //$fset=array_reverse($fset);
  $fset_clean=array();
  foreach ($fset as $v => $w){
      $w=str_replace("|", " | ", $w);
      $next=explode("}{", $w);
      //echo "meret: ".count($next)."<br>";
      foreach ($next as $f1 => $f2){
        $part="";
        //echo "(".$v."; ".$f1."): ";
        $tok = strtok($f2, " ");
        while ($tok !== false) {

          if (strpos($tok, "+")!==false || strpos($tok, "^")!==false || strpos($tok, "|")!==false || strpos($tok, "#SLOT")!==false){
            $tok=str_replace("{","", $tok);
            $tok=str_replace("}","", $tok);
            $tok=str_replace("\"","", $tok);
            //echo $tok."&nbsp;&nbsp;&nbsp;";
            $part=$part.$tok." ";
          }

          $tok=strtok(" ");
        }
        //echo $part;
        if (count($next)==1){
          $fset_clean[]=str_replace("&nbsp;"," ",$part);
        }
        else {
          $fset_clean[$v][]=str_replace("&nbsp;"," ",$part);
        }
        //print "<br>";
        //print "<br>".$v." ==> ".$f1." -> ".$f2;
      }
  }


/*
** subtitutions (SLOT; |)
*/
  $analysis=array();
  $count_analyses=0;
  foreach ($fset_clean as $x => $y){
    if (!is_array($y)){
      //print $x." -> ".$y."<br>";
      if ($count_analyses==0){
        $expl=explode("|", $y);
        $analysis[$count_analyses]=$expl;
        ++$count_analyses;
      }
      else if ($count_analyses>0){
        $analysis[$count_analyses]=$analysis[$count_analyses-1];
        $temp1=explode("|", $y);
        $analysis[$count_analyses]=array(); /* ??? */
        foreach ($analysis[$count_analyses-1] as $e1 => $e2){
          foreach ($temp1 as $t1 => $t2){
            if (!in_array($e2, $analysis[$count_analyses])){
              //print "x:".$x."<br>";
              $analysis[$count_analyses][]=preg_replace("/#SLOT0#/", $t2, $e2, 1);
              //print "<br>t2:".$t2."  e2:".$e2."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;REPL: ".preg_replace("/#SLOT0#/", $t2, $e2, 1)."<br>";
            }

          }
        }
        ++$count_analyses;
      }
    }
    else {
      foreach ($y as $y1 => $y2){
        //print $x.",".$y1." -> ".$y2."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        $temp2=explode("|", $y2);
        $analysis[$count_analyses]=array();  /* ??? */
        foreach ($analysis[$count_analyses-1] as $e3 => $e4){
          foreach ($temp2 as $t21 => $t22){
            if (!in_array($e4, $analysis[$count_analyses])){
              //print "y1:".$y1."<br>";
              $analysis[$count_analyses][]=preg_replace("/#SLOT".$y1."#/", $t22, $e4, 1);
              //print "<br>t22:".$t22."  e4:".$e4."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;REPL: ".preg_replace("/#SLOT".$y1."#/", $t22, $e4, 1)."<br>";
            }

          }
        }
        ++$count_analyses;
      }
      //print "<br>";
    }
  }


/*
** write output
*/
  $full=array();
  foreach ($analysis[count($analysis)-1] as $r1 => $r2){
    //if (strpos($r2, "Prefix+"))
      print "<u>".$word_form."</u> ".$r2."<br>";
  }
  print "<br>";
}

ob_end_clean();

?>