<?php

namespace Icinga\Module\Director\Tables;

use Icinga\Module\Director\Objects\IcingaHost;
use Icinga\Module\Director\Objects\IcingaServiceSet;
use Icinga\Module\Director\Web\Table\QuickTable;

class IcingaServiceSetServiceTable extends QuickTable
{
    protected $set;

    protected $title;

    /** @var IcingaHost */
    protected $host;

    protected $searchColumns = array(
        'service',
    );

    public function getColumns()
    {
        return array(
            'id'             => 's.id',
            'service_set_id' => 's.service_set_id',
            'host_id'        => 'ss.host_id',
            'service_set'    => 'ss.object_name',
            'service'        => 's.object_name',
            'object_type'    => 's.object_type',
        );
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function setHost(IcingaHost $host)
    {
        $this->host = $host;
        return $this;
    }

    public function setServiceSet(IcingaServiceSet $set)
    {
        $this->set = $set;
        return $this;
    }

    protected function renderTitles($row)
    {
        if ($this->host) {
            return $this->renderHostTitles($row);
        } else {
            return parent::renderTitles($row);
        }
    }

    protected function getActionUrl($row)
    {
        if ($this->host) {
            $params = array(
                'name'    => $this->host->getObjectName(),
                'service' => $row->service,
                'set'     => $row->service_set
            );

            return $this->url('director/host/servicesetservice', $params);
        } else {
            $params = array(
                'name' => $row->service,
                'set'  => $row->service_set
            );

            return $this->url('director/service', $params);
        }
    }

    public function getTitles()
    {
        $view = $this->view();
        return array(
            'service' => $this->title ?: $view->translate('Servicename'),
        );
    }

    protected function renderHostTitles($row)
    {
        $view = $this->view();
        // Hint: row is an array of titles!?!?!?
        $title = $view->escape(array_shift($row));

        $htm = "<thead>\n  <tr>\n";

        $deleteLink = $view->qlink(
            $view->translate('Remove'),
            'director/host/removeset',
            array(
                'name' => $this->host->getObjectName(),
                'setId' => $this->set->id
            ),
            array(
                'class' => 'icon-cancel',
                'style' => 'float: right; font-weight: normal',
                'title' => $view->translate('Remove this set from this host')
            )
        );

        $htm .= '    <th>' . $view->escape($title) . "$deleteLink</th>\n";

        return $htm . "  </tr>\n</thead>\n";
    }

    public function getUnfilteredQuery()
    {
        return $this->db()->select()->from(
            array('s' => 'icinga_service'),
            array()
        )->joinLeft(
            array('ss' => 'icinga_service_set'),
            'ss.id = s.service_set_id',
            array()
        )->order('s.object_name');
    }

    public function getBaseQuery()
    {
        return $this->getUnfilteredQuery()->where(
            's.service_set_id = ?',
            $this->set->id
        );
    }
}
