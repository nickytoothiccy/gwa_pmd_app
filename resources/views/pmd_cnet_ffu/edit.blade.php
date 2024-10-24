@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Equipment</h2>
    <form action="{{ route('pmd_cnet_ffu.update', $equipmentItem->Equipment) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="Equipment">Equipment</label>
            <input type="text" class="form-control" id="Equipment" name="Equipment" value="{{ $equipmentItem->Equipment }}" required>
        </div>
        
        <div class="form-group">
            <label for="Parent">Parent</label>
            <input type="text" class="form-control" id="Parent" name="Parent" value="{{ $equipmentItem->Parent }}" required>
        </div>
        
        <div class="form-group">
            <label for="Network">Network</label>
            <input type="text" class="form-control" id="Network" name="Network" value="{{ $equipmentItem->Network }}" required>
        </div>
        
        <div class="form-group">
            <label for="Port">Port</label>
            <input type="text" class="form-control" id="Port" name="Port" value="{{ $equipmentItem->Port }}" required>
        </div>
        
        <div class="form-group">
            <label for="CNX_Sequence">CNX Sequence</label>
            <input type="number" class="form-control" id="CNX_Sequence" name="CNX_Sequence" value="{{ $equipmentItem->CNX_Sequence }}" required>
        </div>
        
        <div class="form-group">
            <label for="Comment">Comment</label>
            <textarea class="form-control" id="Comment" name="Comment" rows="3">{{ $equipmentItem->Comment }}</textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Update Equipment</button>
        <a href="{{ route('pmd_cnet_ffu.equipment_search') }}" class="btn btn-secondary">Cancel</a>
    </form>
    
    <button id="sendEmailBtn" class="btn btn-warning mt-3" style="display: none;">
        <i class="fas fa-exclamation-circle"></i> Send Email
    </button>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        checkJsonFileStatus();

        document.getElementById('sendEmailBtn').addEventListener('click', function() {
            sendEmail();
        });
    });

    function checkJsonFileStatus() {
        fetch('{{ route("pmd_cnet_ffu.check_json_file") }}')
            .then(response => response.json())
            .then(data => {
                if (data.hasData) {
                    document.getElementById('sendEmailBtn').style.display = 'inline-block';
                } else {
                    document.getElementById('sendEmailBtn').style.display = 'none';
                }
            });
    }

    function sendEmail() {
        fetch('{{ route("pmd_cnet_ffu.send_email") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Email sent successfully');
                checkJsonFileStatus();
            } else {
                alert('Failed to send email');
            }
        });
    }
</script>
@endsection
