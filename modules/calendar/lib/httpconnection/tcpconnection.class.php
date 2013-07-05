<?php
/**
 * Calendar 
 * 
 * @package    contrexx
 * @subpackage module_calendar
 * @author     Comvation <info@comvation.com>
 * @copyright  CONTREXX CMS - COMVATION AG
 * @version    1.00
 */


/**
 * tcpconnection
 * 
 * @package    contrexx
 * @subpackage module_calendar
 * @author     Comvation <info@comvation.com>
 * @copyright  CONTREXX CMS - COMVATION AG
 * @version    1.00
 */
class tcpconnection
{
	/**
	 * The connection-resource.
	 *
	 * @var resource
	 */
	private $connection;
	/**
	 * The address of the server.
	 *
	 * @var string
	 */
	protected $host;
	/**
	 * The port of the server.
	 *
	 * @var int
	 */
	protected $port;
	/**
	 * Use SSL?
	 *
	 * @var bool
	 */
	protected $ssl;

	/**
	 * Constructor
	 *
	 * @param string $host
	 * @param int $port
	 * @param bool $ssl
	 */
	public function __construct($host,$port,$ssl=false)
	{
		$this->connection=false;
		$this->host=$host;
		$this->port=$port;
		$this->ssl=$ssl;
	}

	/**
	 * Connect to the server.
	 *
	 * @return bool
	 */
	public function connect()
	{
		if ($this->ssl===true)
		{
			$this->connection=fsockopen('ssl://'.$this->host,$this->port);
		}
		else
		{
			$this->connection=fsockopen($this->host,$this->port);
		}
		return $this->connected();
	}

	/**
	 * Disconnect from the server.
	 *
	 */
	public function disconnect()
	{
		if ($this->connected()===true)
		{
			fclose($this->connection);
			$this->connection=false;
		}
	}

	/**
	 * Check if the connection has been established.
	 *
	 * @return bool
	 */
	public function connected()
	{
		return ($this->connection!==false);
	}

	/**
	 * Set the timeout.
	 *
	 * @param int $sec
	 * @param int $msec
	 * @return bool
	 */
	public function settimeout($sec,$msec=0)
	{
		if ($this->connected()===false)
		{
			return false;
		}
		return stream_set_timeout($this->connection,$sec,$msec);
	}

	/**
	 * Set the mode of stream-blocking.
	 *
	 * @param int $mode
	 * @return bool
	 */
	public function setblocking($mode)
	{
		if ($this->connected()===false)
		{
			return false;
		}
		return stream_set_blocking($this->connection,$mode);
	}

	/**
	 * Read all data from the stream.
	 *
	 * @return mixed
	 */
	public function read()
	{
		if ($this->connected()===false)
		{
			return false;
		}
		$response='';
		while ($data=$this->readline())
		{
			$response.=$data;
		}
		return $response;
	}

	/**
	 * Read a line of data from the stream.
	 *
	 * @return mixed
	 */
	public function readline()
	{
		if ($this->connected()===false)
		{
			return false;
		}
		return fgets($this->connection);
	}

	/**
	 * Read a number of bytes from the stream.
	 *
	 * @param int $bytes
	 * @return mixed
	 */
	public function readbytes($bytes)
	{
		if ($this->connected()===false)
		{
			return false;
		}
		return fread($this->connection,$bytes);
	}

	/**
	 * Write data to the stream.
	 *
	 * @param string $data
	 * @return bool
	 */
	public function write($data)
	{
		if ($this->connected()===false)
		{
			return false;
		}
		fwrite($this->connection,$data);
		return true;
	}
}
?>