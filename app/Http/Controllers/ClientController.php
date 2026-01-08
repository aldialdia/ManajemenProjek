<?php

namespace App\Http\Controllers;

use App\Http\Requests\Client\StoreClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    /**
     * Display a listing of clients.
     */
    public function index(Request $request): View
    {
        $query = Client::withCount('projects');

        // Search by name or company
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('company', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $clients = $query->orderBy('name')->paginate(10);

        return view('clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new client.
     */
    public function create(): View
    {
        return view('clients.create');
    }

    /**
     * Store a newly created client.
     */
    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = Client::create($request->validated());

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client created successfully.');
    }

    /**
     * Display the specified client.
     */
    public function show(Client $client): View
    {
        $client->load([
            'projects' => function ($query) {
                $query->withCount('tasks')->latest();
            }
        ]);

        return view('clients.show', compact('client'));
    }

    /**
     * Show the form for editing the client.
     */
    public function edit(Client $client): View
    {
        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified client.
     */
    public function update(StoreClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified client.
     */
    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client deleted successfully.');
    }
}
