<?php


namespace funyx\api\Logger;


use Phalcon\Logger\Formatter\AbstractFormatter;

class LineFormat extends AbstractFormatter
{
    protected string $date_format;
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
        $this->setDateFormat('c');
        $this->uid = $uid;
    }

    public function format( \Phalcon\Logger\Item $item ): string
    {
        $s = $this->getFormattedDate();
        $s .= ' ['.$this->uid.']';
        $s .= ' ['.$item->getName().']';
        $s .= ' - '.$item->getMessage();
        return $s;
    }
}
