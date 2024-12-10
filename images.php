<!DOCTYPE html>
<html>

<head>
    <style>
        html * {
            box-sizing: border-box;
        }

        p {
            margin: 0;
        }

        .upload__box {
            padding: 40px;
        }

        .upload__inputfile {
            width: 0.1px;
            height: 0.1px;
            opacity: 0;
            overflow: hidden;
            position: absolute;
            z-index: -1;
        }

        .upload__btn {
            display: inline-block;
            font-weight: 600;
            color: #fff;
            text-align: center;
            min-width: 116px;
            padding: 5px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid;
            background-color: #4045ba;
            border-color: #4045ba;
            border-radius: 10px;
            line-height: 26px;
            font-size: 14px;
        }

        .upload__btn:hover {
            background-color: unset;
            color: #4045ba;
            transition: all 0.3s ease;
        }

        .upload__btn-box {
            margin-bottom: 10px;
        }

        .upload__img-wrap {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }

        .upload__img-box {
            width: 200px;
            padding: 0 10px;
            margin-bottom: 12px;
        }

        .upload__img-close {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: rgba(0, 0, 0, 0.5);
            position: absolute;
            top: 10px;
            right: 10px;
            text-align: center;
            line-height: 24px;
            z-index: 1;
            cursor: pointer;
        }

        .upload__img-close:after {
            content: '\2716';
            font-size: 14px;
            color: white;
        }

        .img-bg {
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            position: relative;
            padding-bottom: 100%;
        }
    </style>

</head>

<body>
    <div class="upload__box">
        <form id="uploadForm" action="upload.php" method="post" enctype="multipart/form-data">
            <div class="upload__btn-box">
                <label class="upload__btn">
                    <p>Upload images</p>
                    <input type="file" name="files[]" multiple="" data-max_length="20" class="upload__inputfile">
                </label>
            </div>
            <div class="upload__img-wrap"></div>
            <input type="submit" value="SUBMIT">
        </form>
    </div>
</body>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('.upload__inputfile').on('change', function (e) {
            var imgWrap = $(this).closest('.upload__box').find('.upload__img-wrap');
            var maxLength = parseInt($(this).attr('data-max_length'));
            var files = e.target.files;
            var filesArr = Array.prototype.slice.call(files);
            filesArr.forEach(function (f) {
                if (!f.type.match('image.*')) {
                    return;
                }
                if (imgWrap.find('.upload__img-box').length >= maxLength) {
                    return;
                }
                var reader = new FileReader();
                reader.onload = function (e) {
                    var html = "<div class='upload__img-box'><div style='background-image: url(" + e.target.result + ")' data-file='" + f.name + "' class='img-bg'><div class='upload__img-close'></div></div></div>";
                    imgWrap.append(html);
                }
                reader.readAsDataURL(f);
            });
        });

        $('#uploadForm').on('submit', function (e) {
            e.preventDefault(); // Prevent default form submission
            var formData = new FormData(this); // Create FormData object
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    // Handle success response
                    console.log(response);
                },
                error: function (xhr, status, error) {
                    // Handle error
                    console.error(error);
                }
            });
        });

        $('body').on('click', ".upload__img-close", function (e) {
            $(this).parent().parent().remove();
        });
    });

</script>


</html>
