$(function() {
    $('.like').click(function() {
        var likeButton = $(this);
        id = likeButton.data('id');
        url = likeButton.data('url');
        $.ajax({
            type: 'POST',
            url: url,
            data: {id: id},
            success: function(res) {
                let count = JSON.parse(res).count;
                likeButton.siblings('.like-count').text(count);
            },
            error: function() {
                alert('Sorry, unable to like.')
            }
        });
    });
});