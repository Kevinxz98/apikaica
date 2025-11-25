<?php

namespace App\Http\Controllers;
use App\Models\Services;
use Illuminate\Support\Str;


use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Services::orderBy('created_at', 'desc')->get();
        return response()->json($services);
    }

    public function listActiveServices()
    {
        $services = Services::where('status', 'active')->orderBy('created_at', 'desc')->get();
        return response()->json($services);
    }

    public function agentBySlug($slug)
    {
        $service = Services::where('slug', $slug)->first();

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        return response()->json($service);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lead' => 'required|string|max:2550',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'price_monthly' => 'required|numeric',
            'features' => 'nullable|array',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('services', 'public');
            $validated['image'] = $path; // 👉 Guardas solo la ruta, no el archivo
        }

        $validated['slug'] = Str::slug($validated['name']);
        $validated['price_yearly'] = $validated['price_monthly'] * 11;

        $service = Services::create([
            ...$validated,
            'features' => json_encode($validated['features'] ?? []),
        ]);


        return response()->json(['message' => 'Service created successfully', 'service' => $service], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $service = Services::find($id);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        return response()->json($service);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $service = Services::find($id);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'lead' => 'sometimes|required|string|max:2550',
            'description' => 'sometimes|required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'images_quill.*' => 'nullable|image|max:4096',
            'price_monthly' => 'sometimes|required|numeric',
            'features' => 'nullable|array',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $paths = [];

        if ($request->hasFile('images_quill')) {
            foreach ($request->file('images_quill') as $image) {
                $paths[] = asset('storage/' . $image->store('services', 'public'));

            }
        }

        $service->images_quill = array_merge($service->images_quill ?? [], $paths);


        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('services', 'public');
            $validated['image'] = $path;
        }

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if (isset($validated['price_monthly'])) {
            $validated['price_yearly'] = $validated['price_monthly'] * 11;
        }

        if (isset($validated['features'])) {
            $validated['features'] = json_encode($validated['features']);
        }

        $service->update($validated);

        return response()->json([
            'message' => 'Service updated successfully', 
            'service' => $service,
            'uploaded_urls' => $paths 
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $servicio = Services::find($id);

        if (!$servicio) {
            return response()->json(['message' => 'Servicio no encontrado'], 404);
        }

        $servicio->delete();
        return response()->json(['message' => 'Servicio eliminado con éxito']);
    }
}
