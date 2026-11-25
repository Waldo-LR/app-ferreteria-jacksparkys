<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Catálogo de Productos - Ferretería Jack Sparkys S.A.C.')] class extends Component
{
};
?>
<div class="space-y-4">
    @livewire('productos-index')
</div>
