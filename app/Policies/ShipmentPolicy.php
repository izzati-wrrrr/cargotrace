<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ShipmentPolicy
{
    private function isStaff(User $user): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }

    // Daftar dibatasi di controller: customer hanya melihat shipment miliknya.
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Shipment $shipment): bool
    {
        return $user->isCustomer() ? $shipment->customer_id === $user->id : true;
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function update(User $user, Shipment $shipment): Response|bool
    {
        if (! $this->isStaff($user)) {
            return false;
        }

        return $shipment->current_status->isEditable()
            ? true
            : Response::deny('Shipment yang sudah diambil kurir tidak bisa diedit. Hanya statusnya yang bisa diperbarui.');
    }

    public function changeStatus(User $user, Shipment $shipment): bool
    {
        return $this->isStaff($user);
    }

    public function delete(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin();
    }
}