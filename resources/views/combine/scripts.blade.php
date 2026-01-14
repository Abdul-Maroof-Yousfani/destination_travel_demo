<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
<script src="{{ url('assets/js/custom.js') }}"></script>
<script>
	new WOW().init();
	const _alert = (msg, type = 'success') => {
        const icons = {
            success: '#28a745',
            error: '#f27474',
            warning: '#ffc107',
            info: '#17a2b8'
        };
        Swal.fire({
            position: 'top-end',
            icon: type,
            title: `<span style="font-size: 15px;">${msg}</span>`,
            showConfirmButton: false,
            timer: 2000,
            toast: true,
            background: '#fff',
            customClass: { title: 'custom-title', popup: 'custom-toast' },
            iconColor: icons[type] || icons.success
        });
    }
    const confirmationModal = (title = 'Are You Sure?', icon = 'warning') => {
        return Swal.fire({
            title, icon,
            confirmButtonText: "Yes",
            cancelButtonText: "Cancel",
            showCancelButton: true,
            showCloseButton: true
        });
    };
    // Usage :)
    // let alMsg = 'Are you sure these details are proper?';
    // confirmationModal(alMsg).then((result) => {
    //     if (result.isConfirmed) {
    //         _alert('Is Confirmed');
    //     } else {
    //         _alert('Is Cancel', 'error');
    //         return;
    //     }
    //     console.log('ggs')
    // });
    const _confirm = async (title = 'Are You Sure?', showCancel = true, icon = 'warning', confirmBtn = 'Yes', cancelBtn = 'Cancel') => {
        const result = await Swal.fire({
            title,
            icon,
            confirmButtonText: confirmBtn,
            cancelButtonText: cancelBtn,
            showCancelButton: showCancel,
            showCloseButton: showCancel,
            allowOutsideClick: false,
        });
        return result.isConfirmed;
    };
    // (async () => {
    //     if (await _confirm('Are you sure you want to delete this entry?', 'warning')) {
    //         // perform delete
    //     }
    // })();
    $(document).ready(function () {
        $('.logoutBtn').on('click', function (e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('admin.logout') }}",
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function () {
                    window.location.href = "{{ route('home') }}";
                },
                error: function (xhr) {
                    alert('Logout failed.');
                }
            });
        });
    });
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('.clickable-row').on('click', function () {
        let href = $(this).data('href');
        if (href) {
            window.location.href = href;
        }
    });
</script>
