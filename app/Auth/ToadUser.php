<?php

namespace App\Auth;

use Illuminate\Auth\GenericUser;

// GenericUser accepte un tableau associatif et implémente Authenticatable - pas besoin d'une classe complète
class ToadUser extends GenericUser {}
