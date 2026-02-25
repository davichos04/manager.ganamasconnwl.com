/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$(document).ready(function () {

    var setMenuActive = function () {
        $("#accordionSidebar > li > a").each(function () {
            var key = $(this).attr('href').replace('/', '');
            var actual = window.location.pathname.split("/");
            //alert(window.location.pathname);
            try {
                $('#' + key).removeClass("active");
                if (actual[1].indexOf(key) >= 0) {
                    $('#' + key).addClass("active");
                }
            } catch (e) {

            }
        });
    }
    setMenuActive();
    var dataTableConfig = {
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json'
        },
//        responsive: true,
        order: [0, 'desc']
    };

    $('#table-content').DataTable(dataTableConfig);
    $('#table-files').DataTable(dataTableConfig);
    $('#table-update').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json'
        },
        order: [0, 'desc']
    });

    $('#table-emails').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json'
        },
        responsive: true,
        order: [0, 'desc'],
        ajax: {
            type: 'POST',
            url: "/application/detailtable"
        }
    });

    $('#pts-confirm-button').click(function () {
        let fid = $('#fid').val();
        Swal.fire({
            title: '¿Estás seguro?',
            html: "Por favor, confirma que deseas procesar el archivo<br>Considera que al procesarlo, los puntos se cargarán, publicarán y ya no se podrá detener el proceso.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4BB543',
            cancelButtonColor: '#d33',
            confirmButtonText: '¡Carga los puntos, estoy segur@!',
            cancelButtonText: '¡No!, ¡Espera!',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).addClass('hidden');
                $('#pts-cancel-button').addClass('hidden');
                $.post("/points/success", {fid: fid}).done(function (data) {
                    switch (data) {
                        case '1':
                            Swal.fire({
                                title: '¡Muy bien!',
                                html: 'Se ha terminado la asignación y publicación de los puntos a los usuarios indicados.',
                                icon: 'success',
                                confirmButtonText: 'Aceptar',
                                allowOutsideClick: false
                            }).then((result) => {
                                window.location.href = '/points';
                            });
                            break;
                        default:
                            Swal.fire({
                                title: '¡Oh no!',
                                html: 'Ocurrió un problema al actualizar el estatus del archivo<br>es probable que hayas dado doble clic.<br><br>Verifica que tu archivo tenga la estructura correcta<br><br>Código de error: ' + data,
                                icon: 'error',
                                confirmButtonText: '¡Aceptar!',
                                allowOutsideClick: false
                            }).then((result) => {
                                window.location.href = '/points';
                            });
                            break;
                    }
                });
            }
        });
    });

    $('#pts-cancel-button').click(function () {
        let fid = $('#fid').val();
        Swal.fire({
            title: '¿Estás seguro?',
            html: "Por favor, confirma que no deseas procesar el archivo<br>Considera que al no procesarlo, los puntos no serán asignados",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4BB543',
            cancelButtonColor: '#d33',
            confirmButtonText: '¡Cancela la carga, estoy segur@!',
            cancelButtonText: '¡No!, ¡Espera!',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).addClass('hidden');
                $('#pts-confirm-button').addClass('hidden');

                $.post("/points/cancel", {fid: fid}).done(function (data) {
                    switch (data) {
                        case '1':
                            Swal.fire({
                                title: '¡Muy bien!',
                                html: 'Se ha cancelado el proceso de envio del mailing a los destinatarios',
                                icon: 'success',
                                confirmButtonText: 'Aceptar',
                                allowOutsideClick: false
                            }).then((result) => {
                                window.location.href = '/points';
                            });
                            break;
                        default:
                            Swal.fire({
                                title: '¡Oh no!',
                                html: 'Ocurrió un problema al actualizar el estatus del archivo<br>es probable que hayas dado doble clic.<br><br>Verifica que tu archivo tenga el estatus correcto<br><br>Código de error: ' + data,
                                icon: 'error',
                                confirmButtonText: '¡Llévame ahí!',
                                allowOutsideClick: false
                            }).then((result) => {
                                window.location.href = '/points';
                            });
                            break;
                    }
                });
            }
        })
    });
    $('#pts-release-button').click(function () {
        let fid = $('#fid').val();
        $.post("/points/cancelfile", {fid: fid})
                .done(function (data) {
                    window.location.href = "/points";
                });
    });
    $('#sls-confirm-button').click(function () {
        let fid = $('#fid').val();
        Swal.fire({
            title: '¿Estás seguro?',
            html: "Por favor, confirma que deseas procesar el archivo<br>Considera que al procesarlo, los pedidos se actualizarán y ya no se podrá detener el proceso",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4BB543',
            cancelButtonColor: '#d33',
            confirmButtonText: '¡Actualiza los pedidos, estoy segur@!',
            cancelButtonText: '¡No!, ¡Espera!',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).addClass('hidden');
                $('#sls-cancel-button').addClass('hidden');
                $.post("/sales/success", {fid: fid}).done(function (data) {
                    switch (data) {
                        case '1':
                            Swal.fire({
                                title: '¡Muy bien!',
                                html: 'Se ha iniciado el proceso de actualización de pedidos.',
                                icon: 'success',
                                confirmButtonText: 'Aceptar',
                                allowOutsideClick: false
                            }).then((result) => {
                                window.location.href = '/sales';
                            });
                            break;
                        default:
                            Swal.fire({
                                title: '¡Oh no!',
                                html: 'Ocurrió un problema al actualizar el estatus del archivo<br>es probable que hayas dado doble clic.<br><br>Verifica que tu archivo tenga la estructura correcta<br><br>Código de error: ' + data,
                                icon: 'error',
                                confirmButtonText: '¡Aceptar!',
                                allowOutsideClick: false
                            }).then((result) => {
                                window.location.href = '/sales';
                            });
                            break;
                    }
                });
            }
        });
    });

    $('#sls-cancel-button').click(function () {
        let fid = $('#fid').val();
        Swal.fire({
            title: '¿Estás seguro?',
            html: "Por favor, confirma que no deseas procesar el archivo<br>Considera que al no procesarlo, los pedidos no se actualizarán",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4BB543',
            cancelButtonColor: '#d33',
            confirmButtonText: '¡Cancela la carga, estoy segur@!',
            cancelButtonText: '¡No!, ¡Espera!',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).addClass('hidden');
                $('#sls-confirm-button').addClass('hidden');
                $.post("/sales/cancel", {fid: fid}).done(function (data) {
                    switch (data) {
                        case '1':
                            Swal.fire({
                                title: '¡Muy bien!',
                                html: 'Se ha cancelado el proceso de actualización de los pedidos.',
                                icon: 'success',
                                confirmButtonText: 'Aceptar',
                                allowOutsideClick: false
                            }).then((result) => {
                                window.location.href = '/sales';
                            });
                            break;
                        default:
                            Swal.fire({
                                title: '¡Oh no!',
                                html: 'Ocurrió un problema al actualizar el estatus del archivo<br>es probable que hayas dado doble clic.<br><br>Verifica que tu archivo tenga la estructura correcta<br><br>Código de error: ' + data,
                                icon: 'error',
                                confirmButtonText: '¡Llévame ahí!',
                                allowOutsideClick: false
                            }).then((result) => {
                                window.location.href = '/sales';
                            });
                            break;
                    }
                });
            }
        })
    });
    $('#sls-release-button').click(function () {
        let fid = $('#fid').val();
        $.post("/sales/cancelfile", {fid: fid})
                .done(function (data) {
                    window.location.href = "/points";
                });
    });
    // // Validate Email
    // function validateEmail() {
    //     const email = $("#username");

    //     let regex = /^([_\-\.0-9a-zA-Z]+)@([_\-\.0-9a-zA-Z]+)\.([a-zA-Z]){2,7}$/;
    //     let s = email.value;
    //     if (regex.test(s)) {
    //         // email.classList.remove("is-invalid");
    //         return true;
    //     } else {
    //         // email.classList.add("is-invalid");
    //         return false;
    //     }


    // }


    // $("#loginButton").click(function () {
    //     if (validateEmail()) {
    //         return false;
    //     } else {
    //         Swal.fire({
    //             title: '¡Oh no!',
    //             html: 'Para continuar, debes ingresar un correo electrónico',
    //             icon: 'error',
    //             confirmButtonText: 'De acuerdo!',
    //             allowOutsideClick: false
    //         });
    //     }

    // });
    let usersDivChart = $('#usersChart');
    if (usersDivChart.length > 0) {
        let json = [];
        $.post("/application/chartusers").done(function (response) {
            json = JSON.parse(response);
        });
        var ctx = document.getElementById("usersChart");
        /*var myPieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ["Con complemento", "Sin Complemento", "Bajas"],
                datasets: [{
                        data: [55, 30, 15],
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
                        hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }],
            },
            options: {
                maintainAspectRatio: true,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: true,
                    caretPadding: 10,
                },
                legend: {
                    display: true
                },
//                cutoutPercentage: 80,
            },
        });*/

    }


    let divChart = $('#div-chart');
    if (divChart.length > 0) {
        let total = [];
        let used = [];
        let aval = [];
        let labls = [];

        $.post("/application/chartcodes").done(function (response) {
            let json = JSON.parse(response);
            $.each(json, function (i, item) {
                total[i] = item.total;
                used[i] = item.used;
                aval[i] = item.aval;
                labls[i] = item.code_type;
            });

            const ctx = document.getElementById('myChart');
            const data = {
                labels: labls,
                datasets: [
                    {
                        label: 'Total',
                        data: total,
                        borderWidth: 1
                    },
                    {
                        label: 'Usados',
                        data: used,
                        borderWidth: 1
                    },
                    {
                        label: 'Disponibles',
                        data: aval,
                        borderWidth: 1
                    }
                ]
            };
            const config = {
                type: 'bar',
                data: data,
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            };
            new Chart(ctx, config);
        });
    }
});