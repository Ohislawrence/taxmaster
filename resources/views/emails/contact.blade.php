@component('mail::message')
# New contact form submission

**Name:** {{ $data['name'] }}

**Email:** {{ $data['email'] }}

**Company:** {{ $data['company'] ?? '—' }}

**Topic:** {{ ucfirst($data['subject']) }}

---

{{ $data['message'] }}

@endcomponent
