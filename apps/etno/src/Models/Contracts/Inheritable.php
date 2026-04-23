<?php

namespace Metafori\Etno\Models\Contracts;

use Illuminate\Database\Eloquent\Model;

interface Inheritable
{
    public function isInheritable(string $attribute): bool;

    public function isInherited(string $attribute): bool;

    public function isInheritableAndInherited(string $attribute): bool;

    public function getParent(): ?Model;

    public function resolveInheritableAttribute(string $attribute): mixed;
}
