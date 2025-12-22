@extends('layouts.staff')

@section('title', 'Developer Access')

@section('content')
<div class="container">
    <div class="row justify-content-center" style="margin-top: 5rem;">
        <div class="col-md-5">
            <div class="card shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="bi bi-code-slash" style="font-size: 4rem; color: #06A3DA;"></i>
                        </div>
                        <h2 class="fw-bold mb-2">Developer Access</h2>
                        <p class="text-muted">Enter developer password to continue</p>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="/staff/developer/authenticate">
                        @csrf
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">
                                <i class="bi bi-shield-lock me-2"></i>Developer Password
                            </label>
                            <input 
                                type="password" 
                                class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                id="password" 
                                name="password" 
                                placeholder="Enter password"
                                autofocus
                                required
                            >
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-unlock me-2"></i>Authenticate
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            This area is restricted to developers only
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
