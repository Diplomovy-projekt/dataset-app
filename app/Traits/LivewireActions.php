<?php

namespace App\Traits;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use ReflectionClass;
use ReflectionMethod;

trait LivewireActions
{
    public function handleResponse($action, $message = null)
    {
        // Handle array configuration
        if (is_array($action)) {
            // First process any action
            if (isset($action['action'])) {
                $actionValue = $action['action'];

                // Handle refresh action
                if ($actionValue === 'refresh') {
                    $this->dispatch('refresh');
                    return;
                }
                // Handle refreshComputed action
                elseif ($actionValue === 'refreshComputed') {
                    $this->dispatch('reset-computed-properties');
                }
                // Handle direct route specification in the action array
            }
            elseif (isset($action['route'])) {
                $routeName = $action['route'];
                $params = $action['params'] ?? [];
                if (filter_var($routeName, FILTER_VALIDATE_URL) || strpos($routeName, '/') === 0) {
                    // For full paths or URLs
                    $this->redirect($routeName);
                } else {
                    // For named routes
                    $this->redirectRoute($routeName, $params);
                }
                return;
            }

            // Then process any flash message
            if (isset($action['type']) && isset($action['message'])) {
                $this->dispatch('flash-msg', type: $action['type'], message: $action['message']);
                return;
            }

            return;
        }

        // Handle standard flash messages (success, error, warning, info)
        if (is_string($action) && in_array($action, ['success', 'error', 'warning', 'info'])) {
            $this->dispatch('flash-msg', type: $action, message: $message);
        }
    }

    #[On('reset-computed-properties')]
    public function resetComputedProperties()
    {
        $reflection = new ReflectionClass($this);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            // Check if the method has the #[Computed] attribute
            $attributes = $method->getAttributes(Computed::class);

            if (!empty($attributes)) {
                $computedName = $method->name; // Get the method name directly
                unset($this->$computedName);   // Unset the cached value in Livewire
            }
        }
    }
}
