{{ getDoctype() }}
<html lang="ru">
	<head>
		<meta charset="utf-8"/>
		{{ getTitle() }}
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta content="width=device-width, initial-scale=1" name="viewport"/>
		<meta content="" name="description"/>
		<meta content="Olympia Digital" name="author"/>

		{{ assets.outputCss() }}
		{{ assets.outputJs() }}

		<link rel="shortcut icon" href="/favicon.ico"/>
	</head>
    <body class="page-500-full-page">
        <div class="row">
            <div class="col-md-12 page-500">
                <div class=" number font-red"> 500 </div>
                <div class=" details">
                    <h3>Oops! Something went wrong.</h3>
                    <p> We are fixing it! Please come back in a while.
                        <br/> </p>
                    <p>
                        <a href="{{ url('/') }}" class="btn red btn-outline"> Return home </a>
                        <br>
					</p>
                </div>
            </div>
        </div>
    </body>
</html>