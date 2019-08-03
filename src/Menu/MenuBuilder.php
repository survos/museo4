<?php


namespace App\Menu;


use Survos\LandingBundle\Menu\LandingMenuBuilder;

class MenuBuilder extends LandingMenuBuilder
{

    public function createMainMenu(array $options)
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav mr-auto');

        $menu->addChild('Home', ['route' => 'survos_landing'])
            ->setAttribute('icon', 'fa fa-home');

        $menu->addChild('s3', [
            'route' => 's3_index',
        ]);

        $menu->addChild('exhibits', ['route' => 'exhibit_index']);
        $menu->addChild('admin', ['route' => 'easyadmin']);
        $menu->addChild('player', ['route' => 'player']);
        $menu->addChild('app', ['route' => 'museo_app']);

        // ... add more children

        return $this->cleanupMenu($menu);
    }



}