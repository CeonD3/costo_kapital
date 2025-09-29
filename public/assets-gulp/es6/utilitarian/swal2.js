const swal2 =  Object.freeze({
    show: function (args) {
        Swal.close(); let onOk = args.onOk, onCancel = args.onCancel; delete args['onOk']; delete args['onCancel'];  args.allowEscapeKey = false; args.allowOutsideClick = false;
        return Swal.fire(args).then((result) => { if (result.isConfirmed) { if (onOk) onOk(); } else { if (onCancel) onCancel();} })
    },
    loading: function (hide = true) {
        if (hide === false) { return Swal.close(); } else { return Swal.fire({ text: 'Cargando ...', allowEscapeKey: false, allowOutsideClick: false, didOpen: () => { Swal.showLoading() } }); }
    }
});

window.swal2 = swal2;