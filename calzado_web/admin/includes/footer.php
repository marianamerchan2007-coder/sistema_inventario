<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<div class="toast-container position-fixed top-0 end-0 p-3"
    id="toastContainer"
    style="z-index: 9999;">
</div>

<script>
function mostrarToast(mensaje, tipo = 'danger') {

    const iconos = {
        success: '<i class="bi bi-check-lg text-white me-2"></i>',
        danger: '<i class="bi bi-x-circle-fill text-white me-2"></i>',
        warning: '<i class="bi bi-exclamation-triangle-fill me-2"></i>',
    };

    const toastContainer =
        document.getElementById('toastContainer');

    const toast =
        document.createElement('div');

    toast.className =
        `toast align-items-center text-bg-${tipo} border-0 show mb-2`;

    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${iconos[tipo] || ''}${mensaje}
            </div>
        </div>
    `;

    toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 4000);
}
</script>

<?php if(isset($_SESSION['toast'])): ?>

<script>

document.addEventListener('DOMContentLoaded', function(){

    mostrarToast(
        "<?= $_SESSION['toast']; ?>",
        "<?= $_SESSION['toast_tipo']; ?>"
    );

});

</script>

<?php
unset($_SESSION['toast']);
unset($_SESSION['toast_tipo']);
endif;
?>

<script>
document.addEventListener('DOMContentLoaded', function(){

    const sidebar = document.querySelector('#sidebar');
    const overlay = document.querySelector('#overlay');

    document.addEventListener('click', function(e){

        // Abrir
        if(e.target.closest('#btnSidebar')){

            sidebar.classList.add('show');
            overlay.classList.add('show');
        }

        // Cerrar con X
        if(e.target.closest('#btnCerrarSidebar')){

            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        }

    });

    // Cerrar al tocar el overlay
    overlay.addEventListener('click', function(){

        sidebar.classList.remove('show');
        overlay.classList.remove('show');

    });

});


//filtro sidebar
document.querySelectorAll('.filtro-link').forEach(link => {

    link.addEventListener('click', function(e){

        e.preventDefault();

        const categoria =
            this.dataset.categoria;

        const estado =
            this.dataset.estado;

        document.querySelectorAll('.fila-producto')
        .forEach(fila => {

            if(categoria){

                fila.style.display =
                    fila.dataset.categoria === categoria
                    ? ''
                    : 'none';
            }

            if(estado){

                fila.style.display =
                    fila.dataset.estado === estado
                    ? ''
                    : 'none';
            }

        });

    });

});


document.addEventListener('DOMContentLoaded', () => {

    const links = document.querySelectorAll('.filtro-link');
    const sidebar = document.querySelector('#sidebar');
    const overlay = document.querySelector('#overlay');

    links.forEach(link => {

        link.addEventListener('click', function(){

            links.forEach(item =>
                item.classList.remove('active')
            );

            this.classList.add('active');

            // cerrar sidebar en móvil
            if(window.innerWidth <= 768){

                sidebar.classList.remove('show');

                if(overlay){
                    overlay.classList.remove('show');
                }
            }

        });

    });

});

</script>

<style>
.toast-container{
    max-width: 100%;
    padding: 12px;
}

.toast{
    width: 100%;
    max-width: 380px;
    min-width: auto;

    border-radius: 14px !important;
    font-size: 15px;
    font-weight: 500;

    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,.12);
}

.toast-body{
    padding: 12px 15px;
    display: flex;
    align-items: center;
    gap: 8px;
    word-break: break-word;
}

.toast-body i{
    font-size: 18px;
    flex-shrink: 0;
}

@media (max-width:768px){

    .toast-container{
        top: 0 !important;
        right: 3px !important;
        left: auto !important;

        display: flex;
        flex-direction: column;
        align-items: flex-end;

        padding: 0;
    }

    .toast{
        width: auto;
        max-width: calc(100vw - 20px);
        font-size: 13px;
    }

    .toast-body{
        padding: 10px 12px;
    }

    .toast-body i{
        font-size: 15px;
    }
}
</style>