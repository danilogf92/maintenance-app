<?php

// Los comandos se descubren en app/Console/Commands.
\Illuminate\Support\Facades\Schedule::command('reports:clean-previews')->everyFiveMinutes()->withoutOverlapping();
