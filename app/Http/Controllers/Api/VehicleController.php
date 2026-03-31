<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleService;
use App\Models\VehicleWarrantyClaim;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    // ── Vehicles ─────────────────────────────────────────

    public function index(): JsonResponse
    {
        $vehicles = Vehicle::withCount(['services', 'warrantyClaims'])
            ->withSum('services', 'cost')
            ->get();

        return response()->json($vehicles);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:car,motorbike',
            'make' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:2099',
            'plate_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $vehicle = Vehicle::create($validated);

        return response()->json($vehicle, 201);
    }

    public function show(Vehicle $vehicle): JsonResponse
    {
        $vehicle->load(['services', 'warrantyClaims']);

        return response()->json($vehicle);
    }

    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:car,motorbike',
            'make' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:2099',
            'plate_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $vehicle->update($validated);

        return response()->json($vehicle);
    }

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $vehicle->delete();

        return response()->json(null, 204);
    }

    // ── Services ─────────────────────────────────────────

    public function services(Vehicle $vehicle): JsonResponse
    {
        return response()->json($vehicle->services);
    }

    public function storeService(Request $request, Vehicle $vehicle): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'service_type' => 'nullable|string|max:100',
            'mileage' => 'nullable|integer|min:0',
            'cost' => 'required|numeric|min:0',
            'date' => 'required|date',
            'next_service_date' => 'nullable|date',
            'next_service_mileage' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $service = $vehicle->services()->create($validated);

        return response()->json($service, 201);
    }

    public function updateService(Request $request, VehicleService $service): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'sometimes|string|max:255',
            'service_type' => 'nullable|string|max:100',
            'mileage' => 'nullable|integer|min:0',
            'cost' => 'sometimes|numeric|min:0',
            'date' => 'sometimes|date',
            'next_service_date' => 'nullable|date',
            'next_service_mileage' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $service->update($validated);

        return response()->json($service);
    }

    public function destroyService(VehicleService $service): JsonResponse
    {
        if ($service->receipt_path) {
            Storage::disk('public')->delete($service->receipt_path);
        }
        $service->delete();

        return response()->json(null, 204);
    }

    public function uploadServiceReceipt(Request $request, VehicleService $service): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|max:10240',
        ]);

        if ($service->receipt_path) {
            Storage::disk('public')->delete($service->receipt_path);
        }

        $path = $request->file('file')->store('vehicle-receipts', 'public');
        $service->update(['receipt_path' => $path]);

        return response()->json($service);
    }

    // ── Warranty Claims ──────────────────────────────────

    public function warrantyClaims(Vehicle $vehicle): JsonResponse
    {
        return response()->json($vehicle->warrantyClaims);
    }

    public function storeWarrantyClaim(Request $request, Vehicle $vehicle): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'claim_number' => 'nullable|string|max:100',
            'status' => 'nullable|in:pending,approved,rejected,completed',
            'date_filed' => 'required|date',
            'date_resolved' => 'nullable|date',
            'cost_covered' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $claim = $vehicle->warrantyClaims()->create($validated);

        return response()->json($claim, 201);
    }

    public function updateWarrantyClaim(Request $request, VehicleWarrantyClaim $claim): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'sometimes|string|max:255',
            'claim_number' => 'nullable|string|max:100',
            'status' => 'sometimes|in:pending,approved,rejected,completed',
            'date_filed' => 'sometimes|date',
            'date_resolved' => 'nullable|date',
            'cost_covered' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $claim->update($validated);

        return response()->json($claim);
    }

    public function destroyWarrantyClaim(VehicleWarrantyClaim $claim): JsonResponse
    {
        $claim->delete();

        return response()->json(null, 204);
    }

    // ── Summary ──────────────────────────────────────────

    public function summary(): JsonResponse
    {
        $vehicles = Vehicle::with(['services', 'warrantyClaims'])->get();

        $data = $vehicles->map(function ($v) {
            return [
                'id' => $v->id,
                'name' => $v->name,
                'type' => $v->type,
                'total_spent' => $v->services->sum('cost'),
                'total_services' => $v->services->count(),
                'total_warranty_covered' => $v->warrantyClaims->where('status', 'completed')->sum('cost_covered'),
                'pending_claims' => $v->warrantyClaims->where('status', 'pending')->count(),
                'next_service' => $v->services->whereNotNull('next_service_date')->sortBy('next_service_date')->first()?->only(['next_service_date', 'next_service_mileage']),
            ];
        });

        return response()->json($data);
    }
}
