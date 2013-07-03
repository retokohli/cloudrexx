<?php
/**
 * @package PHPClassCollection
 * @subpackage HTTPConnection
 * @link http://php-classes.sourceforge.net/ PHP Class Collection
 * @author Dennis Wronka <reptiler@users.sourceforge.net>
 */
/**
 * @package PHPClassCollection
 * @subpackage HTTPConnection
 * @link http://php-classes.sourceforge.net/ PHP Class Collection
 * @author Dennis Wronka <reptiler@users.sourceforge.net>
 * @version 1.4
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL 2.1
 */
require_once ASCMS_MODULE_PATH . '/calendar/lib/httpconnection/tcpconnection.class.php'; 
 
class httpconnection extends tcpconnection
{
	/**
	 * The address of the web-server.
	 *
	 * @var string
	 */
	private $httphost;
	/**
	 * The port of the web-server.
	 *
	 * @var int
	 */
	private $httpport;
	/**
	 * The address of the proxy-server.
	 *
	 * @var mixed
	 */
	private $proxyhost;
	/**
	 * The port of the proxy-server.
	 *
	 * @var int
	 */
	private $proxyport;
	/**
	 * The user-agent used for identification.
	 *
	 * @var string
	 */
	private $useragent;

	/**
	 * Constructor
	 *
	 * @param string $httphost
	 * @param int $httpport
	 * @param bool $ssl
	 * @param mixed $proxyhost
	 * @param int $proxyport
	 * @param string $useragent
	 */
	public function __construct($httphost,$httpport=80,$ssl=false,$proxyhost=false,$proxyport=3128,$useragent='PHP/ReptilerHTTPClass')
	{
		$this->httphost=$httphost;
		$this->httpport=$httpport;
		$this->proxyhost=$proxyhost;
		$this->proxyport=$proxyport;
		$this->useragent=$useragent;
		if ($this->proxyhost!==false)
		{
			parent::__construct($this->proxyhost,$this->proxyport,$ssl);
		}
		else
		{
			parent::__construct($this->httphost,$this->httpport,$ssl);
		}
	}

	/**
	 * Decode the reply.
	 *
	 * @param string $reply
	 * @return array
	 */
	private function decodereply($reply)
	{
		$headend=strpos($reply,"\r\n\r\n")+2;
		$head=substr($reply,0,$headend);
		$httpversion=substr($head,5,3);
		$contentlength='';
		$contentlengthstart=strpos($head,'Content-Length:');
		if ($contentlengthstart!==false)
		{
			$contentlengthstart+=16;
			$contentlengthend=strpos($head,"\r\n",$contentlengthstart);
			$contentlength=substr($head,$contentlengthstart,$contentlengthend-$contentlengthstart);
		}
		if ($httpversion=='1.0')
		{
			$datastart=$headend+2;
			$body=substr($reply,$datastart,strlen($reply)-$datastart);
		}
		elseif ($httpversion=='1.1')
		{
			$encoding='';
			$encodingstart=strpos($head,'Transfer-Encoding:');
			if ($encodingstart!==false)
			{
				$encodingstart+=19;
				$encodingend=strpos($head,"\r\n",$encodingstart);
				$encoding=substr($head,$encodingstart,$encodingend-$encodingstart);
			}
			if ($encoding=='chunked')
			{
				$datasizestart=$headend+2;
				$datasizeend=strpos($reply,"\r\n",$datasizestart);
				$datasize=hexdec(trim(substr($reply,$datasizestart,$datasizeend-$datasizestart)));
				$body='';
				while ($datasize>0)
				{
					$chunkstart=$datasizeend+2;
					$body.=substr($reply,$chunkstart,$datasize);
					$datasizestart=$chunkstart+$datasize+2;
					$datasizeend=strpos($reply,"\r\n",$datasizestart);
					$datasize=hexdec(trim(substr($reply,$datasizestart,$datasizeend-$datasizestart)));
				}
			}
			else
			{
				$datastart=$headend+2;
				$datasize=$contentlength;
				$body=substr($reply,$datastart,$datasize);
			}
		}
		$code=substr($head,9,3);
		$serverstart=strpos($head,'Server:')+8;
		$serverend=strpos($head,"\r\n",$serverstart);
		$server=substr($head,$serverstart,$serverend-$serverstart);
		$contenttype='';
		$contenttypestart=strpos($head,'Content-Type:');
		if ($contenttypestart!==false)
		{
			$contenttypestart+=14;
			$contenttypeend=strpos($head,"\r\n",$contenttypestart);
			$contenttype=substr($head,$contenttypestart,$contenttypeend-$contenttypestart);
		}
		$location='';
		$locationstart=strpos($head,'Location:');
		if ($locationstart!==false)
		{
			$locationstart+=10;
			$locationend=strpos($head,"\r\n",$locationstart);
			$location=substr($head,$locationstart,$locationend-$locationstart);
			$location_array=explode('?',$location);
			$parameters='';
			if (isset($location_array[1]))
			{
				$parameters=$location_array[1];
			}
			$location=array('uri'=>$location_array[0],'parameters'=>$parameters);
			if (empty($parameters))
			{
				unset($location['parameters']);
			}
		}
		$cookies=array();
		$cookiestart=strpos($head,'Set-Cookie:');
		while ($cookiestart!==false)
		{
			$cookiestart+=12;
			$cookieend=strpos($head,"\r\n",$cookiestart);
			$cookie=substr($head,$cookiestart,$cookieend-$cookiestart);
			$cookie_array=explode(';',$cookie);
			$expirydate='';
			$path='';
			for ($x=0;$x<count($cookie_array);$x++)
			{
				$cookie_array[$x]=explode("=",$cookie_array[$x]);
				if ($x==0)
				{
					$name=$cookie_array[$x][0];
					$value=$cookie_array[$x][1];
				}
				else
				{
					if (trim($cookie_array[$x][0])=='expires')
					{
						$expirydate=array('string'=>$cookie_array[$x][1],'timestamp'=>strtotime($cookie_array[$x][1]));
					}
					elseif (trim($cookie_array[$x][0])=='path')
					{
						$path=$cookie_array[$x][1];
					}
				}
			}
			$cookie=array('name'=>$name,'value'=>$value,'path'=>$path,'expirydate'=>$expirydate);
			if (empty($path))
			{
				unset($cookie['path']);
			}
			if (empty($expirydate))
			{
				unset($cookie['expirydate']);
			}
			$cookies[]=$cookie;
			$cookiestart=strpos($head,'Set-Cookie:',$cookieend);
		}
		$headdata=array('raw'=>$head,'httpversion'=>$httpversion,'code'=>$code,'server'=>$server,'contentlength'=>$contentlength,'contenttype'=>$contenttype,'location'=>$location,'cookies'=>$cookies);
		if ((empty($contentlength)) && ($contentlength!=0))
		{
			unset($headdata['contentlength']);
		}
		if (empty($contenttype))
		{
			unset($headdata['contenttype']);
		}
		if (empty($location))
		{
			unset($headdata['location']);
		}
		if (empty($cookies))
		{
			unset($headdata['cookies']);
		}
		$data=array('head'=>$headdata,'body'=>$body);
		return $data;
	}

	/**
	 * Send a HEAD-request.
	 *
	 * @param string $uri
	 * @param array $parameters
	 * @param array $cookies
	 * @param string $authuser
	 * @param string $authpassword
	 * @return array
	 */
	public function head($uri='/',$parameters=array(),$cookies=array(),$authuser='',$authpassword='')
	{
		$connected=$this->connect();
		if ($connected===false)
		{
			return false;
		}
		if ((empty($uri)) || ($uri{0}!='/'))
		{
			$uri='/'.$uri;
		}
		if (!empty($parameters))
		{
			$paramstring='?'.implode('&',$parameters);
		}
		else
		{
			$paramstring='';
		}
		if (!empty($cookies))
		{
			$cookiestring='Cookie: '.implode(';',$cookies)."\r\n";
		}
		else
		{
			$cookiestring='';
		}
		if (!empty($authuser))
		{
			$authstring='Authorization: Basic '.base64_encode($authuser.':'.$authpassword)."\r\n";
		}
		else
		{
			$authstring='';
		}
		$host=$this->httphost;
		if ($this->httpport!=80)
		{
			$host.=':'.$this->httpport;
		}
		if ($this->proxyhost!==false)
		{
			if ($this->ssl===true)
			{
				$uri='https://'.$host.$uri;
			}
			else
			{
				$uri='http://'.$host.$uri;
			}
		}
		$this->write('HEAD '.$uri.$paramstring.' HTTP/1.1'."\r\n".'Host: '.$host."\r\n".'User-Agent: '.$this->useragent."\r\n".$cookiestring.$authstring.'Connection: close'."\r\n\r\n");
		$reply=$this->read();
		$this->disconnect();
		$data=$this->decodereply($reply);
		return $data;
	}

	/**
	 * Send a GET-request.
	 *
	 * @param string $uri
	 * @param array $parameters
	 * @param array $cookies
	 * @param string $authuser
	 * @param string $authpassword
	 * @return array
	 */
	public function get($uri='/',$parameters=array(),$cookies=array(),$authuser='',$authpassword='')
	{
		$connected=$this->connect();
		if ($connected===false)
		{
			return false;
		}
		if ((empty($uri)) || ($uri{0}!='/'))
		{
			$uri='/'.$uri;
		}
		if (!empty($parameters))
		{
			$paramstring='?'.implode('&',$parameters);
		}
		else
		{
			$paramstring='';
		}
		if (!empty($cookies))
		{
			$cookiestring='Cookie: '.implode(';',$cookies)."\r\n";
		}
		else
		{
			$cookiestring='';
		}
		if (!empty($authuser))
		{
			$authstring='Authorization: Basic '.base64_encode($authuser.':'.$authpassword)."\r\n";
		}
		else
		{
			$authstring='';
		}
		$host=$this->httphost;
		if ($this->httpport!=80)
		{
			$host.=':'.$this->httpport;
		}
		if ($this->proxyhost!==false)
		{
			if ($this->ssl===true)
			{
				$uri='https://'.$host.$uri;
			}
			else
			{
				$uri='http://'.$host.$uri;
			}
		}
		$this->write('GET '.$uri.$paramstring.' HTTP/1.1'."\r\n".'Host: '.$host."\r\n".'User-Agent: '.$this->useragent."\r\n".$cookiestring.$authstring.'Connection: close'."\r\n\r\n");
		$reply=$this->read();
		$this->disconnect();
		$data=$this->decodereply($reply);
		return $data;
	}

	/**
	 * Send a POST-request.
	 *
	 * @param string $uri
	 * @param array $parameters
	 * @param array $cookies
	 * @param array $fileparameters
	 * @param array $mimetypes
	 * @param string $authuser
	 * @param string $authpassword
	 * @return array
	 */
	public function post($uri='/',$parameters=array(),$cookies=array(),$fileparameters=array(),$mimetypes=array(),$authuser='',$authpassword='')
	{
		$connected=$this->connect();
		if ($connected===false)
		{
			return false;
		}
		if ((empty($uri)) || ($uri{0}!='/'))
		{
			$uri='/'.$uri;
		}
		if (!empty($cookies))
		{
			$cookiestring='Cookie: '.implode(';',$cookies)."\r\n";
		}
		else
		{
			$cookiestring='';
		}
		if (!empty($authuser))
		{
			$authstring='Authorization: Basic '.base64_encode($authuser.':'.$authpassword)."\r\n";
		}
		else
		{
			$authstring='';
		}
		$host=$this->httphost;
		if ($this->httpport!=80)
		{
			$host.=':'.$this->httpport;
		}
		if ($this->proxyhost!=false)
		{
			if ($this->ssl==true)
			{
				$uri='https://'.$host.$uri;
			}
			else
			{
				$uri='http://'.$host.$uri;
			}
		}
		if (empty($fileparameters))
		{
			if (!empty($parameters))
			{
				$paramstring=implode('&',$parameters);
				$contentlength=strlen($paramstring);
				$this->write('POST '.$uri.' HTTP/1.1'."\r\n".'Host: '.$host."\r\n".'User-Agent: '.$this->useragent."\r\n".$cookiestring.$authstring.'Connection: close'."\r\n");
				$this->write('Content-Type: application/x-www-form-urlencoded'."\r\n".'Content-Length: '.$contentlength."\r\n\r\n".$paramstring);
			}
			else
			{
				$this->write('POST '.$uri.' HTTP/1.1'."\r\n".'Host: '.$host."\r\n".'User-Agent: '.$this->useragent."\r\n".$cookiestring.$authstring.'Connection: close'."\r\n\r\n");
			}
		}
		else
		{
			while (count($mimetypes)<count($fileparameters))
			{
				$mimetypes[]='application/octet-stream';
			}
			$params=array();
			for ($x=0;$x<count($parameters);$x++)
			{
				$param=explode('=',$parameters[$x]);
				$params[]=array('name'=>$param[0],'value'=>$param[1]);
			}
			$fileparams=array();
			for ($x=0;$x<count($fileparameters);$x++)
			{
				$fileparam=explode('=',$fileparameters[$x]);
				$fileparams[]=array('name'=>$fileparam[0],'file'=>$fileparam[1],'mimetype'=>$mimetypes[$x]);
			}
			$boundary='-------------------------'.substr(md5(uniqid()),0,15);
			$content='';
			for ($x=0;$x<count($fileparams);$x++)
			{
				$postfile=fopen($fileparams[$x]['file'],'r');
				$filecontent=fread($postfile,filesize($fileparams[$x]['file']));
				fclose($postfile);
				$content.='--'.$boundary."\r\n";
				$content.='Content-Disposition: form-data; name="'.$fileparams[$x]['name'].'"; filename="'.$fileparams[$x]['file'].'"'."\r\n";
				$content.='Content-Type: '.$fileparams[$x]['mimetype']."\r\n\r\n";
				$content.=$filecontent."\r\n";
			}
			for ($x=0;$x<count($params);$x++)
			{
				$content.='--'.$boundary."\r\n";
				$content.='Content-Disposition: form-data; name="'.$params[$x]['name'].'"'."\r\n\r\n".$params[$x]['value']."\r\n";
			}
			$content.='--'.$boundary.'--'."\r\n";
			$contentlength=strlen($content);
			$this->write('POST '.$uri.' HTTP/1.1'."\r\n".'Host: '.$host."\r\n".'User-Agent: '.$this->useragent."\r\n".$cookiestring.$authstring.'Connection: close'."\r\n");
			$this->write('Content-Type: multipart/form-data; boundary='.$boundary."\r\n".'Content-Length: '.$contentlength."\r\n\r\n");
			$this->write($content);
		}
		$reply=$this->read();
		$this->disconnect();
		$data=$this->decodereply($reply);
		return $data;
	}
}
?>