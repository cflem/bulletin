<?php
function dash_fatal ($msg = null, $link = null, $label = null) {
  if ($link === null) $link = 'javascript:history.go(-1);';
  if ($label == null) $label = '&larr; Got It';
  echo tpl(array('message' => $msg, 'link' => $link, 'label' => $label), 'dash_fatal.tpl').PHP_EOL;
  require('footer.php');
  die;
}
function typestr ($type) {
  switch ($type) {
    case 'EMPLOYER':
      return 'Employer';
    case 'EMPLOYEE':
      return 'Employee';
    default:
      return 'Team Member';
  }
}
function rating_format ($rating = null, $typestr = 'Employer') {
  return '<span class="ratingdata" data-rating="'.(is_null($rating) ? 'undef' : number_format($rating, 1)).'"></span>';
}
function draw_norate_p () {
?>
      <div class="job">
        <p class="jobtitle"><a href="post.php">Nothing to Rate Yet!</a></p>
        <p class="jobpay">Post another ad. It's FREE!</p>
        <p class="jobblurb">None of your ads have received a response since you last visited. In the meantime, be sure to post more to maximize your exposure.<br /><a href="post.php">Post an ad!</a></p>
      </div>
<?php
}
function draw_norate_s () {
?>
      <div class="job">
        <p class="jobtitle"><a href="post.php">Nothing to Rate Yet!</a></p>
        <p class="jobpay">Apply to more ads. It's FREE!</p>
        <p class="jobblurb">None of your applications have received a response since you last visited. In the meantime, be sure to reply to more job postings maximize your exposure.<br /><a href="dash/">Browse ads!</a></p>
      </div>
<?php
}
function draw_noads () {
?>
      <div class="job">
        <p class="jobtitle"><a href="post.php">Post an Ad!</a></p>
        <p class="jobpay">It's FREE!</p>
        <p class="jobblurb">You haven't posted any ads yet! It's a quick and easy way to get connected to the workers you need.<br /><a href="post.php">Post an ad!</a></p>
      </div>
<?php
}
function draw_noapps () {
?>
      <div class="job">
        <p class="jobtitle"><a href="post.php">No Applications Yet!</a></p>
        <p class="jobpay">Post another ad. It's FREE!</p>
        <p class="jobblurb">This ad hasn't received any responses yet. In the meantime, be sure to post more to maximize your exposure.<br /><a href="post.php">Post an ad!</a></p>
      </div>
<?php
}
function draw_rate ($row) {
?>
      <div class="job">
        <p class="jobtitle"><a href="ads.php?id=<?=$row['adid'];?>"><?=htmlentities($row['title']);?></a></p>
        <p class="jobpay">Provided by <a href="profile.php?id=<?=$row['uid'];?>"><?=htmlentities($row['name']);?></a></p>
        <p class="jobdate"><?=date('M j, Y', intval($row['time']));?></p>
        <div class="jobblurb"><div class="rate-widget" data-uid="<?=$row['uid'];?>" data-jid="<?=$row['adid'];?>" data-rating="<?=is_null($row['rating']) ? 'undef' : number_format($row['rating'], 1);?>"></div></div>
      </div>
<?php
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
        <p class="jobstars"><?=rating_format($row['rating']);?></p>
        <p class="jobpay">Pays $<?=number_format($row['pay'], 2);?></p>
        <p class="jobblurb"><?=htmlentities(substr($row['description'], 0, min(strlen($row['description']), 160)));?> <a href="ads.php?id=<?=$row['id'];?>">[...]</a></p>
        </p>
      </div>
<?php
}

function draw_app ($row) {
?>
      <div class="job">
        <a href="#" class="appxbtn" data-rid="<?=$row['id'];?>"></a>
        <a href="#" class="appcbtn" data-rid="<?=$row['id'];?>"></a>
        <p class="jobtitle"><a href="profile.php?id=<?=$row['uid'];?>"><?=htmlentities($row['name']);?></a></p>
        <p class="jobstars"><?=rating_format($row['rating'], 'Employee');?></p>
        <p class="joblocation"><?=is_null($row['address']) ? '<em>No address listed.</em>' : htmlentities($row['address']);?></p>
        <p class="jobblurb"><?=is_null($row['comment']) ? '<em>No comment included.</em>' : htmlentities($row['comment']);?></p>
        </p>
      </div>
<?php
}

// triggers
function app_trigger ($responseid) {
  global $db, $b_config;  

  $result = $db->query('SELECT responses.id, responses.uid AS seeker, responses.adid, responses.comment, ads.title, users.id AS provider, users.name, users.email, users.notify FROM responses INNER JOIN ads ON responses.adid = ads.id INNER JOIN users ON ads.uid = users.id WHERE responses.id = '.intval($responseid).' LIMIT 1') or dash_fatal($db->error);
  if ($result->num_rows < 1) dash_fatal('The ad you\'ve tried to apply to no longer exists.');
  $appinfo = $result->fetch_assoc();
  $result->free();

  $result = $db->query('SELECT users.name, users.email, SUM(ratings.stars) / COUNT(ratings.stars) AS rating FROM users LEFT JOIN ratings ON ratings.rated = users.id WHERE users.id = '.$appinfo['seeker'].' LIMIT 1') or dash_fatal($db->error);
  $uinfo = $result->fetch_assoc();
  $result->free();

  $db->query('INSERT INTO notif (uid, icon, text, link) VALUES ('.$appinfo['provider'].', \'APPLIED\', \'"'.$db->escape_string($appinfo['title']).'" Has Received a Response\', \''.$db->escape_string($b_config['base_url'].'dash/?view='.$appinfo['adid']).'\')') or dash_fatal($db->error);

  if (!$appinfo['notify']) return;

  $options = array(
    'adid' => $appinfo['adid'],
    'adname' => $appinfo['title'],
    'seekername' => $uinfo['name'],
    'seekerrating' => is_null($uinfo['rating']) ? 'has yet to be rated' : 'is rated '.number_format($uinfo['rating'], 1).' stars',
    'seekereml' => $uinfo['email'],
    'seekerid' => $appinfo['seeker'],
  );
  bulletin_mail($appinfo['email'], '"'.$appinfo['title'].'" Has Received a Response', tpl($options, 'app_eml.tpl'));
}
function hire_trigger ($rid) {
  global $db, $b_config;

  $result = $db->query('SELECT responses.adid, responses.uid AS seeker, ads.title, ads.uid, users.name, users.email, users.notify FROM responses INNER JOIN ads ON ads.id = responses.adid INNER JOIN users ON users.id = responses.uid WHERE responses.id = '.intval($rid).' LIMIT 1') or dash_fatal($db->error);
  if ($result->num_rows < 1) dash_fatal('Oops! Something went wrong.');
  $rinfo = $result->fetch_assoc();
  $result->free();

  $result = $db->query('SELECT users.name, users.email FROM users WHERE users.id = '.$rinfo['uid'].' LIMIT 1') or dash_fatal($db->error);
  if ($result->num_rows < 1) dash_fatal('The employer you\'ve applied to no longer has an account with us.');
  $pinfo = $result->fetch_assoc();
  $result->free();

  $db->query('INSERT INTO notif (uid, icon, text, link) VALUES ('.$rinfo['seeker'].', \'HIRED\', \'You\\\'ve been hired for "'.$db->escape_string($rinfo['title']).'"\', \''.$db->escape_string($b_config['base_url'].'dash/ads.php?id='.$rinfo['adid']).'\')') or dash_fatal($db->error);

  if (!$rinfo['notify']) return;

  $options = array(
    'providername' => $pinfo['name'],
    'adid' => $rinfo['adid'],
    'adtitle' => $rinfo['title'],
    'provideremail' => $pinfo['email'],
    'providerid' => $rinfo['uid'],
  );
  bulletin_mail($rinfo['email'], 'You\'ve Been Hired for "'.$rinfo['title'].'"', tpl($options, 'hire_eml.tpl'));
}
?>
