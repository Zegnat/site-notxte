<?php declare(strict_types=1);

session_start();
$auth = isset($_SESSION['logged_in']);


if ($auth && isset($_POST['txt'])) {
    $data = new \SplFileObject('notxte.txt', 'a+');
    $data->fwrite((new DateTimeImmutable())->format(\DateTime::RFC3339) . "\t" . strtr($_POST['txt'], "\n", '') . "\n");
} else if (!file_exists('notxte.txt')) {
    $data = new \SplTempFileObject(1);
} else {
    $data = new \SplFileObject('notxte.txt', 'r');
}
$data->seek(\PHP_INT_MAX);
$data->seek(max(0, $data->key() - 10));
$notes = [];
while ($data->valid()) {
    @list($date, $txt) = explode("\t", rtrim($data->current(), "\n"), 2);
    if ($date !== null && $txt !== null) {
        array_unshift($notes, [
            'date' => \DateTimeImmutable::createFromFormat(\DateTime::RFC3339, $date)->format(\DateTime::RSS),
            'txt' => \preg_replace_callback('%@<(?:(\S+?)\s+)?([a-z][a-z0-9+.-]*:(?://)?(.+?)/?)>%', function ($matches): string {
                    if ($matches[1] === '') $matches[1] = $matches[3];
                    return '<a href="' . $matches[2] . '">@' . $matches[1] . '</a>';
                }, $txt),
        ]);
    }
    $data->next();
}
$data = null; // Close file, just in case.

?><!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>TXT Notes</title>
<?php if ($auth): ?>
    <link rel="stylesheet" href="vendor/awesomplete.css">
<?php endif; ?>
    <style>
        html {
            margin: 0;
            padding: 0;
            background-color: #181818;
            color: #d8d8d8;
        }
        body {
            margin: 0 auto;
            max-width: 60em;
            padding: 1em 2em;
            line-height: 1.4;
        }
        article, form {
            border-bottom: 1px solid #585858;
            padding: 1em 0;
        }
        article div {
            font-size: 120%;
            font-family: serif;
        }
        article footer {
            color: #585858;
            font-size: 80%;
            font-family: sans-serif;
            padding: .2em 0 0;
        }
        body > footer {
            text-align: center;
        }
        body > footer p {
            display: inline-block;
        }
        div.awesomplete {
            width: 100%;
        }
        textarea {
            display: block;
            border: 0;
            width: 100%;
            background-color: #282828;
            color: inherit;
            font-size: 120%;
            font-family: serif;
            line-height: inherit;
            padding: .4em;
            margin: -.4em -.4em 0 -.4em;
        }
        button {
            color: #ab4642;
            font-weight: bold;
            background-color: #181818;
            border: #ab4642 double;
            padding: .1em .4em .2em;
            font-size: 120%;
        }
        button:focus, button:hover {
            background-color: #ab4642;
            color: #181818;
            border-color: #181818;
        }
        .awesomplete > ul {
            color: #181818;
        }
        a:link, a:visited {
            color: #7cafc2;
            text-decoration: none;
        }
        a:hover, a:focus {
            color: #ba8baf;
            text-decoration: underline;
        }
    </style>
  </head>
  <body>
<?php if ($auth): ?>
    <form method="post">
      <textarea name="txt"></textarea>
      <button type="submit">Post</button>
    </form>
<?php endif; ?>
<?php foreach ($notes as $note): ?>
    <article>
      <div><?= $note['txt'] ?></div>
      <footer>Posted <?= $note['date'] ?></footer>
    </article>
<?php endforeach; ?>
    <footer>
      <p>Rendered from <a href="notxte.txt">notxte.txt</a>.</p>
      <p><a href="login.php"><?= $auth ? 'Logout' : 'Login' ?></a></p>
    </footer>
<?php if ($auth): ?>
    <script src="vendor/awesomplete.min.js"></script>
    <script>
        window.fetch('mentions.php').then(response => response.json())
        .then(json => new Awesomplete('textarea', {
            list: json,

            filter: function(text, input) {
                return Awesomplete.FILTER_CONTAINS('@' + text, input.match(/@[^@\s]+$/)[0]);
            },

            data: function(item, input) {
                return {
                    label: item.name + ' (' + item.url + ')',
                    value: '<' + item.name + ' ' + item.url + '>'
                };
            },

            item: function(text, input) {
                return Awesomplete.ITEM('@' + text, input.match(/@[^@\s]+$/)[0]);
            },

            replace: function(text) {
                this.input.value = this.input.value.replace(/@[^@\s]+$/, '@'+text.value+' ');
            }
        }));
    </script>
<?php endif; ?>
  </body>
</html>
