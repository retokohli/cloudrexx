<?php
use Doctrine\Common\Util\Debug as DoctrineDebug;

include('../config/doctrine.php');

/*
$helpers = array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
);
*/

$n = new \Cx\Model\ContentManager\Node();

$c = new \Cx\Model\ContentManager\Node();
$c->setParent($n);


$p = new \Cx\Model\ContentManager\Page();

$p->setLang(1);
$p->setTitle('testpage');
$p->setNode($n);


$em->persist($n);

//$em->flush();

$em->persist($c);

$em->persist($p);

$em->flush();

//now, create a log
$p->setTitle('testpage_changed');
$em->persist($p);

$em->flush();

//now, agiiin
$p->setTitle('testpage_changed_2');
$em->persist($p);

$em->flush();

$repo = $em->getRepository('Gedmo\Loggable\Entity\LogEntry'); // we use default log entry class
//$logs = $repo->getlogEntries($p);
//DoctrineDebug::dump($logs);
echo $p->getTitle()."\n";
$repo->revert($p,1);
echo $p->getTitle()."\n";
$repo->revert($p,2);
echo $p->getTitle()."\n";
$repo->revert($p,1);
echo $p->getTitle()."\n";

/*
DoctrineDebug::dump($n->getPages());

$repo = $em->getRepository("Cx\Model\ContentManager\Node");

$root = $repo->getRootNodes();
$root = $root[0];

DoctrineDebug::dump($root);

DoctrineDebug::dump($root->getPages());
*/