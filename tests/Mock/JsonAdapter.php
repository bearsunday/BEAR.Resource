<?php
namespace BEAR\Resource\Mock;

/**
 * Json adapter
 *
 * @package    BEAR.Framework
 * @subpackage View
 * @author     Akihito Koriyama <akihito.koriyama@gmail.com>
 */
class JsonAdapter implements RenderInterface
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Framework\View.Render::assign()
     */
    public function assign()
    {
        $this->data = $args[0];
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Framework\View.Render::fetch()
     */
    public function fetch($templateFileBase)
    {
        return josn_encode($this->data);
    }
}
