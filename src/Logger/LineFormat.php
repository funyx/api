<?php


namespace funyx\api\Logger;


use Phalcon\Logger\Formatter\AbstractFormatter;

class LineFormat extends AbstractFormatter
{
    protected string $format;
    private string $uid;

    /**
     * LineFormat constructor.
     *
     * @param string $string
     * @param string $string1
     */
    public function __construct(string $uid)
    {
        $this->setDateFormat('D M j H:i:s Y');
        $this->uid = $uid;
    }

    public function format( \Phalcon\Logger\Item $item ): string
    {
        $s = '['.$this->getFormattedDate().']';
	    $s .= ' ['.$_SERVER['REMOTE_ADDR'].']:'.$_SERVER['REMOTE_PORT'];
	    $s .= ' ['.$this->uid.']';
	    $s .= ' ['.$item->getName().']';
        $s .= ' - '.$item->getMessage();
        return $s;
    }
}
