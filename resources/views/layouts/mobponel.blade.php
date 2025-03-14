<div class="mb mobile__ponel"  id="step-mobile-2">
    <ul>
       
      
       
        <li>
            <button onclick="location.href='{{ url('/brifs') }}'" id="step-mobile-4">
                <img src="/storage/icon/brif.svg" alt="">
            </button>
        </li>
        @if (Auth::user()->status == 'coordinator' || Auth::user()->status == 'admin'|| Auth::user()->status == 'partner')
        <li>
            <button onclick="location.href='{{ route('deal.cardinator') }}'" >
                <img src="/storage/icon/deal.svg" alt=""> 
            </button>
        </li>
        <li>
            <button onclick="location.href='{{ url('/chats') }}'"  id="step-6">
                <img src="/storage/icon/chat.svg" alt=""> 
            </button>
        </li>
    @else
        <li>
            <button onclick="location.href='{{ route('deal.user') }}'" id="step-mobile-5">
                <img src="/storage/icon/deal.svg" alt=""> 
            </button>
        </li>
       
    @endif
        @if (Auth::user()->status == 'partner' || Auth::user()->status == 'admin')
            <li>
                <button onclick="location.href='{{ url('/estimate') }}'">
                    <img src="/storage/icon/estimates.svg" alt=""> 
                </button>
            </li>
            
        @endif
  
        <li>
            <button onclick="location.href='{{ url('/profile') }}'" id="step-mobile-6">
                <img src="/storage/icon/profile.svg" alt="">
            </button>
        </li>
        @if (Auth::user()->status == 'admin' )
        <li>
            <button onclick="location.href='{{ url('/admin') }}'">
                <img src="/storage/icon/admin.svg" alt=""> 
            </button>
        </li>
    @endif
        <li>
            <button onclick="location.href='{{ url('/support') }}'"  id="step-mobile-7">
                <img src="/storage/icon/support.svg" alt="">
            </button>
        </li>
    </ul>
</div>