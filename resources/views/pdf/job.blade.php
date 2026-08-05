<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Job Completion Report</title>
<style>
    body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #212529; }
    p { margin: 0 0 8px 0; }
    h5 { margin: 0 0 8px 0; font-size: 14px; }
    .logo { width: 260px; margin-bottom: 10px; }
    .divider { border: none; border-top: 1px solid green; margin: 10px 0; }
    .footer-text { text-align: center; font-size: 11px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    table.bordered th, table.bordered td { border: 1px solid #999; padding: 6px 8px; }
    table.bordered thead th { background-color: #f1f1f1; text-align: left; }
    table.plain td { border: none; padding: 2px 4px; vertical-align: top; }
    table.plain td.label { width: 22%; font-weight: bold; }
    .photo-grid td { width: 50%; padding: 6px; text-align: center; }
    .photo-grid img { width: 100%; max-height: 220px; object-fit: cover; border: 1px solid #ccc; }
    .photo-caption { font-size: 10px; color: #555; margin-top: 4px; }
</style>
</head>
<body>

<img class="logo" src="{{ public_path('build/images/header.png') }}" alt="Oracle Machine Tech">
<br>

<h5>Job Completion Report</h5>
<hr class="divider">

<table class="plain">
    <tr><td class="label">Job</td><td>{{ $job->title }}</td></tr>
    <tr><td class="label">Machine / Site</td><td>{{ $job->machine->name ?? $job->site_name ?? '-' }}</td></tr>
    <tr><td class="label">Priority</td><td>{{ ucfirst($job->priority) }}</td></tr>
    <tr><td class="label">Assigned To</td><td>{{ $job->assignee->name ?? '-' }}</td></tr>
    <tr><td class="label">Due Date</td><td>{{ $job->due_date?->format('d M Y') ?? '-' }}</td></tr>
    <tr><td class="label">Completed At</td><td>{{ $job->completed_at?->format('d M Y, H:i') ?? '-' }}</td></tr>
</table>

@if($job->description)
    <h5>Description</h5>
    <p>{{ $job->description }}</p>
@endif

<h5>Completion Notes</h5>
<p>{{ $job->completion_notes ?: '-' }}</p>

<h5>Proof Photos ({{ $job->photos->count() }})</h5>
@if($job->photos->isEmpty())
    <p>No photos were attached to this job.</p>
@else
    <table class="photo-grid">
        @foreach($job->photos->chunk(2) as $pair)
            <tr>
                @foreach($pair as $photo)
                    @php $absolutePath = public_path('storage/' . $photo->path); @endphp
                    <td>
                        @if(file_exists($absolutePath))
                            <img src="{{ $absolutePath }}" alt="Job photo">
                        @else
                            <p class="photo-caption">(photo file unavailable)</p>
                        @endif
                        <p class="photo-caption">
                            {{ $photo->captured_at?->format('d M Y, H:i') ?? '-' }}
                            @if($photo->location_captured)
                                &middot; {{ $photo->address ?? 'Location captured' }}
                            @else
                                &middot; Location not captured
                            @endif
                        </p>
                    </td>
                @endforeach
                @if($pair->count() === 1)
                    <td></td>
                @endif
            </tr>
        @endforeach
    </table>
@endif

<p class="footer-text">Generated {{ now()->format('d M Y, H:i') }} &middot; Oracle Machine Tech CRM</p>

</body>
</html>
