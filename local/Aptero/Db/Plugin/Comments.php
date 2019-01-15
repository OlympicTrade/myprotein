<?php
namespace Aptero\Db\Plugin;

use Aptero\Db\Plugin\PluginAbstract;

class Comments extends PluginAbstract
{
    protected $comments = array();

    public function load()
    {
        $parentId = $this->getParentId();

        if(!$parentId) {
            return $this;
        }

        $select = $this->getSelect()
            ->where(array('t.' . $this->parentFiled => $parentId));

        $result = $this->fetchAll($select);

        $this->fill($result);

        return $this;
    }

    public function addComment(array $options)
    {
        $this->comments[] = array(
            'id'        => 0,
            'name'      => $options['name'],
            'comment'   => $options['comment'],
        );

        return $this;
    }

    public function getComments()
    {
        if(!$this->loaded) {
            $this->load();
        }

        return $this->comments;
    }

    /**
     * @param $data
     * @return Comments
     */
    public function fill($data)
    {
        foreach($data as $name => $value) {
            $this->addComment($value);
        }

        return $this;
    }
}