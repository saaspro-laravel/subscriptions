<?php

namespace SaasPro\Subscriptions;

use Filament\Panel;


class PluginProvider {

    public function filament(Panel $panel){
        return $panel
            ->discoverResources(in: __DIR__."/Filament/Resources", for: 'SaasPro\\Subscriptions\\Filament\\Resources');
    }

}