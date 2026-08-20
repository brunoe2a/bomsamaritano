<?php

test('a raiz do site redireciona para o dashboard', function () {
    $this->get(route('home'))->assertRedirect(route('dashboard'));
});
