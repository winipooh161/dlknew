<form action="{{ route('commercial.store') }}" method="POST" class="div__create_form">
    @csrf
    <div class="div__create_block">
        <h1><span class="Jikharev">Уважаемый клиент, </span> мы подготовили для Вас коммерческий бриф  </h1>
        <p>Вам необходимо заполнить все поля. Чем больше мы получим информации, тем более точный результат мы сможем гарантировать!</p>
     
        <button type="submit" class="button__icon" ><span>Создать бриф </span> <img src="/storage/icon/plus.svg" alt=""></button>
    </div>
</form>
