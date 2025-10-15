@extends('layouts.app')

@section('title', 'Staff Seating Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Seating Options</span>
                </h6>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">
                            View All Tables
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            Add Table
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            Reservations
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content area with seating canvas -->
        <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Seating Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Save Layout</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">Reset</button>
                    </div>
                </div>
            </div>

            <!-- Seating Canvas -->
            <div class="seating-canvas-container">
                <canvas id="seatingCanvas" width="800" height="600" class="border border-gray-300 bg-white"></canvas>
            </div>

            <!-- Table Information -->
            <div class="mt-4">
                <h3 class="h4">Available Tables</h3>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Table ID</th>
                                <th>Capacity</th>
                                <th>Status</th>
                                <th>Location</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>T-001</td>
                                <td>4</td>
                                <td>Available</td>
                                <td>Front Area</td>
                                <td>
                                    <button class="btn btn-sm btn-primary">Edit</button>
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </td>
                            </tr>
                            <tr>
                                <td>T-002</td>
                                <td>6</td>
                                <td>Reserved</td>
                                <td>Back Area</td>
                                <td>
                                    <button class="btn btn-sm btn-primary">Edit</button>
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </td>
                            </tr>
                            <tr>
                                <td>T-003</td>
                                <td>2</td>
                                <td>Available</td>
                                <td>Window Side</td>
                                <td>
                                    <button class="btn btn-sm btn-primary">Edit</button>
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Simple canvas demonstration
    const canvas = document.getElementById('seatingCanvas');
    const ctx = canvas.getContext('2d');
    
    // Draw sample tables
    ctx.fillStyle = '#4A5568';
    ctx.font = '14px Arial';
    
    // Table 1
    ctx.fillRect(50, 50, 100, 80);
    ctx.fillStyle = 'white';
    ctx.fillText('T-001', 75, 95);
    
    // Table 2
    ctx.fillStyle = '#4A5568';
    ctx.fillRect(200, 50, 120, 80);
    ctx.fillStyle = 'white';
    ctx.fillText('T-002', 235, 95);
    
    // Table 3
    ctx.fillStyle = '#4A5568';
    ctx.fillRect(50, 180, 80, 60);
    ctx.fillStyle = 'white';
    ctx.fillText('T-003', 70, 215);
</script>
@endpush
@endsection
