$(function () {
    if (!window.location.pathname.includes('/capacitaciones')) {
        return;
    }

    let treeData = [];
    let rewardProducts = [];
    let selected = { capId: '', quizId: '', questionId: '' };

    $('#cap-expires').datetimepicker({ format: 'Y-m-d H:i' });
    $('#reward-product').select2({ width: '100%' });

    const toast = (icon, text) => Swal.fire({ toast: true, icon: icon, title: text, position: 'top-end', timer: 1800, showConfirmButton: false });

    const refreshHints = function () {
        $('#quiz-hint').toggleClass('d-none', !!selected.capId);
        $('#question-hint').toggleClass('d-none', !!selected.quizId);
        $('#answer-hint').toggleClass('d-none', !!selected.questionId);
    };

    const renderTree = function () {
        if (!treeData.length) {
            $('#builder-tree').html('<p class="text-muted">No hay registros.</p>');
            return;
        }
        let html = '<ul class="list-group">';
        treeData.forEach(function (cap) {
            html += '<li class="list-group-item"><a href="#" class="tree-cap" data-id="' + cap.id + '"><strong>' + cap.title + '</strong></a>';
            if (cap.quizzes && cap.quizzes.length) {
                html += '<ul class="mt-2">';
                cap.quizzes.forEach(function (quiz) {
                    html += '<li><a href="#" class="tree-quiz" data-id="' + quiz.id + '">' + quiz.title + '</a>';
                    if (quiz.questions && quiz.questions.length) {
                        html += '<ul>';
                        quiz.questions.forEach(function (question) {
                            html += '<li><a href="#" class="tree-question" data-id="' + question.id + '">' + question.question_text.substring(0, 45) + '</a>';
                            if (question.answers && question.answers.length) {
                                html += '<ul>';
                                question.answers.forEach(function (answer) {
                                    html += '<li><a href="#" class="tree-answer" data-id="' + answer.id + '">' + answer.answer_text.substring(0, 40) + '</a></li>';
                                });
                                html += '</ul>';
                            }
                            html += '</li>';
                        });
                        html += '</ul>';
                    }
                    html += '</li>';
                });
                html += '</ul>';
            }
            html += '</li>';
        });
        html += '</ul>';
        $('#builder-tree').html(html);
    };

    const fillRewardProducts = function () {
        const $select = $('#reward-product');
        $select.html('<option value="">Seleccione...</option>');
        rewardProducts.forEach(function (item) {
            $select.append('<option value="' + item.id + '">' + item.label + '</option>');
        });
        $select.trigger('change.select2');
    };

    const clearQuizForm = function () {
        const capId = selected.capId;
        $('#quiz-form')[0].reset();
        $('#quiz-id').val('');
        $('#quiz-cap-id').val(capId || '');
        $('#reward-product').val('').trigger('change');
        $('#quiz-reward-mode').trigger('change');
    };

    const clearQuestionForm = function () {
        const quizId = selected.quizId;
        $('#question-form')[0].reset();
        $('#question-id').val('');
        $('#question-quiz-id').val(quizId || '');
        $('#question-image-preview').hide().attr('src', '');
    };

    const clearAnswerForm = function () {
        const questionId = selected.questionId;
        $('#answer-form')[0].reset();
        $('#answer-id').val('');
        $('#answer-question-id').val(questionId || '');
    };

    const clearCapForm = function () {
        $('#cap-form')[0].reset();
        $('#cap-id').val('');
        $('#thumbnail-preview').hide().attr('src', '');
        $('#media-file-name').text('');
        $('#cap-media-type').trigger('change');
    };

    const findById = function (type, id) {
        let result = null;
        treeData.forEach(function (cap) {
            if (type === 'cap' && cap.id == id) { result = cap; }
            (cap.quizzes || []).forEach(function (quiz) {
                if (type === 'quiz' && quiz.id == id) { result = quiz; }
                (quiz.questions || []).forEach(function (question) {
                    if (type === 'question' && question.id == id) { result = question; }
                    (question.answers || []).forEach(function (answer) {
                        if (type === 'answer' && answer.id == id) { result = answer; }
                    });
                });
            });
        });
        return result;
    };

    const loadData = function () {
        $.get('/capacitaciones/data').done(function (resp) {
            if (!resp.success) {
                Swal.fire('Error', resp.message || 'No se pudo cargar.', 'error');
                return;
            }
            treeData = resp.data || [];
            rewardProducts = resp.products || [];
            fillRewardProducts();
            renderTree();
        });
    };

    const setPreview = function (input, previewSelector, labelSelector) {
        const file = input.files && input.files[0] ? input.files[0] : null;
        if (!file) {
            return;
        }
        if (labelSelector) {
            $(labelSelector).text(file.name);
        }
        if (file.type && file.type.startsWith('image/')) {
            const url = URL.createObjectURL(file);
            $(previewSelector).attr('src', url).show();
        }
    };

    const wireDropZones = function () {
        $('.drop-zone').each(function () {
            const $zone = $(this);
            const inputId = $zone.data('input');
            const $input = $('#' + inputId);

            $zone.on('click', function () { $input.trigger('click'); });
            $zone.on('dragover', function (e) { e.preventDefault(); $zone.addClass('dragover'); });
            $zone.on('dragleave', function () { $zone.removeClass('dragover'); });
            $zone.on('drop', function (e) {
                e.preventDefault();
                $zone.removeClass('dragover');
                const dt = e.originalEvent.dataTransfer;
                if (!dt || !dt.files || !dt.files.length) { return; }
                $input[0].files = dt.files;
                $input.trigger('change');
            });
        });
    };

    $('#cap-media-type').on('change', function () {
        const isFile = this.value === 'image' || this.value === 'pdf';
        $('#media-file-wrap').toggleClass('d-none', !isFile);
        $('#media-url-wrap').toggleClass('d-none', isFile);
    });

    $('#quiz-reward-mode').on('change', function () {
        $('#reward-product-wrap').toggleClass('d-none', this.value !== 'product');
        $('#reward-points-wrap').toggleClass('d-none', this.value !== 'points');
    });

    $('#question-image').on('change', function () {
        setPreview(this, '#question-image-preview');
    });
    $('#thumbnail-file-input').on('change', function () {
        setPreview(this, '#thumbnail-preview');
    });
    $('#media-file-input').on('change', function () {
        setPreview(this, '#not-used', '#media-file-name');
    });

    $('#btn-new-cap').on('click', function () {
        selected = { capId: '', quizId: '', questionId: '' };
        clearCapForm();
        clearQuizForm();
        clearQuestionForm();
        clearAnswerForm();
        refreshHints();
    });

    $('#btn-new-quiz').on('click', function () { clearQuizForm(); refreshHints(); });
    $('#btn-new-question').on('click', function () { clearQuestionForm(); refreshHints(); });
    $('#btn-new-answer').on('click', function () { clearAnswerForm(); refreshHints(); });
    $('#btn-reload-tree').on('click', function () { loadData(); });

    $('#cap-form').on('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        $.ajax({ url: '/capacitaciones/save-capacitacion', type: 'POST', data: fd, processData: false, contentType: false }).done(function (resp) {
            if (resp.success) { toast('success', resp.message); loadData(); } else { Swal.fire('Error', resp.message, 'error'); }
        });
    });

    $('#quiz-form').on('submit', function (e) {
        e.preventDefault();
        if (!$('#quiz-cap-id').val()) { Swal.fire('Atención', 'Primero selecciona/guarda una capacitación.', 'warning'); return; }
        $.post('/capacitaciones/save-quiz', $(this).serialize()).done(function (resp) {
            if (resp.success) { toast('success', resp.message); loadData(); } else { Swal.fire('Error', resp.message, 'error'); }
        });
    });

    $('#question-form').on('submit', function (e) {
        e.preventDefault();
        if (!$('#question-quiz-id').val()) { Swal.fire('Atención', 'Primero selecciona/guarda un quiz.', 'warning'); return; }
        const fd = new FormData(this);
        $.ajax({ url: '/capacitaciones/save-question', type: 'POST', data: fd, processData: false, contentType: false }).done(function (resp) {
            if (resp.success) { toast('success', resp.message); loadData(); } else { Swal.fire('Error', resp.message, 'error'); }
        });
    });

    $('#answer-form').on('submit', function (e) {
        e.preventDefault();
        if (!$('#answer-question-id').val()) { Swal.fire('Atención', 'Primero selecciona/guarda una pregunta.', 'warning'); return; }
        $.post('/capacitaciones/save-answer', $(this).serialize()).done(function (resp) {
            if (resp.success) { toast('success', resp.message); loadData(); } else { Swal.fire('Error', resp.message, 'error'); }
        });
    });

    const bindDelete = function (btn, url, idField, afterFn) {
        $(btn).on('click', function () {
            const id = $(idField).val();
            if (!id) { return; }
            Swal.fire({ title: '¿Eliminar?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, eliminar' }).then(function (r) {
                if (!r.isConfirmed) { return; }
                $.post(url, { id: id }).done(function (resp) {
                    if (resp.success) {
                        toast('success', resp.message);
                        if (afterFn) { afterFn(); }
                        loadData();
                    }
                });
            });
        });
    };

    bindDelete('#btn-delete-cap', '/capacitaciones/delete-capacitacion', '#cap-id', function () {
        selected = { capId: '', quizId: '', questionId: '' };
        clearCapForm(); clearQuizForm(); clearQuestionForm(); clearAnswerForm(); refreshHints();
    });
    bindDelete('#btn-delete-quiz', '/capacitaciones/delete-quiz', '#quiz-id', function () {
        selected.quizId = ''; selected.questionId = '';
        clearQuizForm(); clearQuestionForm(); clearAnswerForm(); refreshHints();
    });
    bindDelete('#btn-delete-question', '/capacitaciones/delete-question', '#question-id', function () {
        selected.questionId = '';
        clearQuestionForm(); clearAnswerForm(); refreshHints();
    });
    bindDelete('#btn-delete-answer', '/capacitaciones/delete-answer', '#answer-id', function () {
        clearAnswerForm(); refreshHints();
    });

    $('#builder-tree').on('click', '.tree-cap', function (e) {
        e.preventDefault();
        const row = findById('cap', $(this).data('id'));
        if (!row) { return; }

        selected.capId = String(row.id);
        selected.quizId = '';
        selected.questionId = '';

        $('#cap-id').val(row.id);
        $('#cap-form [name="title"]').val(row.title);
        $('#cap-form [name="alias"]').val(row.alias);
        $('#cap-form [name="description"]').val(row.description);
        $('#cap-form [name="media_type"]').val(row.media_type).trigger('change');
        $('#cap-form [name="media_url"]').val(row.media_url);
        $('#cap-form [name="expires_at"]').val(row.expires_at);
        $('#cap-published').prop('checked', parseInt(row.published, 10) === 1);
        if (row.thumbnail) {
            $('#thumbnail-preview').attr('src', row.thumbnail).show();
        } else {
            $('#thumbnail-preview').hide().attr('src', '');
        }

        clearQuizForm();
        clearQuestionForm();
        clearAnswerForm();
        refreshHints();
    });

    $('#builder-tree').on('click', '.tree-quiz', function (e) {
        e.preventDefault();
        const row = findById('quiz', $(this).data('id'));
        if (!row) { return; }

        selected.capId = String(row.capacitacion_id);
        selected.quizId = String(row.id);
        selected.questionId = '';

        $('#quiz-id').val(row.id);
        $('#quiz-cap-id').val(row.capacitacion_id);
        $('#quiz-form [name="title"]').val(row.title);
        $('#quiz-form [name="max_attempts"]').val(row.max_attempts);
        $('#quiz-form [name="pass_score"]').val(row.pass_score);
        $('#quiz-form [name="reward_mode"]').val(row.reward_mode).trigger('change');
        $('#reward-product').val(row.reward_product_id).trigger('change');
        $('#quiz-form [name="reward_points"]').val(row.reward_points);
        $('#quiz-form [name="reward_limit"]').val(row.reward_limit);
        $('#quiz-form [name="reward_awarded_count"]').val(row.reward_awarded_count);
        $('#quiz-published').prop('checked', parseInt(row.published, 10) === 1);

        clearQuestionForm();
        clearAnswerForm();
        refreshHints();
    });

    $('#builder-tree').on('click', '.tree-question', function (e) {
        e.preventDefault();
        const row = findById('question', $(this).data('id'));
        if (!row) { return; }

        selected.quizId = String(row.quiz_id);
        selected.questionId = String(row.id);

        $('#question-id').val(row.id);
        $('#question-quiz-id').val(row.quiz_id);
        $('#question-form [name="question_text"]').val(row.question_text);
        $('#question-form [name="type"]').val(row.type);
        $('#question-form [name="ordering"]').val(row.ordering);
        $('#question-published').prop('checked', parseInt(row.published, 10) === 1);
        if (row.image) {
            $('#question-image-preview').attr('src', row.image).show();
        } else {
            $('#question-image-preview').hide().attr('src', '');
        }

        clearAnswerForm();
        refreshHints();
    });

    $('#builder-tree').on('click', '.tree-answer', function (e) {
        e.preventDefault();
        const row = findById('answer', $(this).data('id'));
        if (!row) { return; }
        selected.questionId = String(row.question_id);
        $('#answer-id').val(row.id);
        $('#answer-question-id').val(row.question_id);
        $('#answer-form [name="answer_text"]').val(row.answer_text);
        $('#answer-correct').prop('checked', parseInt(row.is_correct, 10) === 1);
        refreshHints();
    });

    wireDropZones();
    $('#cap-media-type').trigger('change');
    $('#quiz-reward-mode').trigger('change');
    refreshHints();
    loadData();
});
