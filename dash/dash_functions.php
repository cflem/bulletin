<?php
function dash_fatal ($msg = null, $link = null, $label = null) {
  if ($link === null) $link = 'javascript:history.go(-1);';
  if ($label == null) $label = '&larr; Got It';
  echo tpl(array('message' => $msg, 'link' => $link, 'label' => $label), 'dash_fatal.tpl').PHP_EOL;
  require('footer.php');
  die;
}
function draw_ad ($row) {
?>
      <div class="job">
        <a href="#" class="jobxbtn"></a>
        <p class="jobtitle"><a href="ads.php?id=<?=$row['id'];?>"><?=htmlentities($row['title']);?></a></p>
<?php
  if (is_null($row['cat_name']))
    echo '        <p class="jobcat">Uncategorized</p>'.PHP_EOL;
  else
    echo '        <p class="jobcat">'.htmlentities($row['cat_name']).'</p>'.PHP_EOL;
?>
        <p class="joblocation"><?=htmlentities($row['location']);?></p>
<?php
  if (is_null($row['rating']))
    echo '        <p class="jobstars">Employer Not Rated</p>'.PHP_EOL;
  else
    echo '        <p class="jobstars">'.intval($row['rating']).' Star Employer</p>'.PHP_EOL;
?>
        <p class="jobpay">Pays $<?=number_format($row['pay'], 2);?></p>
        <p class="jobblurb"><?=htmlentities(substr($row['description'], 0, min(strlen($row['description']), 160)));?> <a href="ads.php?id=<?=$row['id'];?>">[...]</a></p>
        </p>
      </div>
<?php
}
?>