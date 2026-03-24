<?php

namespace App\Http\Livewire;

use Livewire\Component;

class CartComponent extends Component
{
    public $cart = [];
    public $quantities = [];

    public function mount()
    {
        $this->cart = session('cart', []);
        foreach ($this->cart as $item) {
            $this->quantities[$item['product']['id']] = $item['quantity'];
        }
    }


    public function incrementQuantity($productId)
    {
        $this->quantities[$productId] = ($this->quantities[$productId] ?? 0) + 1;
        $this->updateCart($productId);
        $this->cart = session('cart', []); // <-- Atualiza o carrinho
    }

    public function decrementQuantity($productId)
    {
        $current = $this->quantities[$productId] ?? 0;
        if ($current > 0) {
            $this->quantities[$productId] = $current - 1;
            $this->updateCart($productId);
            $this->cart = session('cart', []); // <-- Atualiza o carrinho
        }
    }

    public function updateCart($productId)
    {
        foreach ($this->cart as &$item) {
            if ($item['product']['id'] == $productId) {
                $item['quantity'] = $this->quantities[$productId];
            }
        }
        session(['cart' => $this->cart]);
        $this->cart = session('cart', []);
    }

    public function render()
    {
        return view('livewire.cart-component', [
            'cart' => $this->cart,
            'quantities' => $this->quantities,
        ]);
    }
}
