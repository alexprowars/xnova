## Communication

- Always respond to the user in Russian.

## Development Commands

- Build assets: `npm run build`
- Run tests: `composer test`
- Run static analysis: `composer analyse`
- When sandboxing is enabled, run PHPStan without parallel processing by adding `--debug` (for example, `composer analyse -- --debug`), because the parallel runner requires a local TCP socket.

Use the Composer scripts where available because they encode the project's expected tooling.

## Coding Conventions

- Follow existing code boundaries. Put new code in the part of the application that owns the behavior.
- Prefer existing helpers, actions, services, model patterns, and Filament conventions already present in the target area.
- PHP classes use PascalCase; methods and variables use camelCase; constants use UPPER_SNAKE_CASE.
- Database tables and columns use snake_case. Table names are plural; foreign keys are singular model name plus `_id`.
- Blade view filenames use snake_case.
- PHP and Blade files use tabs for indentation. Follow `.editorconfig` for other file types.
- Do not use variable interpolation in PHP strings. Use single-quoted strings and explicit concatenation instead.
- Do not add redundant casts when a value already has the required type.
- Use `empty()` and `!empty()` to check whether an array is empty instead of comparing it with `[]`.
- Do not declare closures as `static`.
- Do not add an explicit `: void` return type to closures unless it is required by the current PHPStan level.
- Keep files UTF-8 with LF line endings.
- Do not add a trailing blank line at the end of a file when the file did not already have one.
- Prefer `CarbonImmutable` over mutable `Carbon` for new date logic.
- Do not edit generated or dependency directories such as `vendor`, `node_modules`, `public/build`, or framework-published assets unless the task explicitly requires it.

## Laravel Notes

- The project autoloads `app/functions.php`.
- `Model::unguard()` is enabled by default; do not add redundant mass-assignment workarounds unless a specific model requires guarded behavior.
- Do not rely on `$fillable` or `$guarded` as the primary security mechanism.
- Do not pass unchecked `$request->all()` directly into `create()`, `update()`, `fill()`, or `forceFill()`.
- In Eloquent queries, use an arrow function (`fn`) when a callback makes a single method call. If it performs multiple query operations or contains multiple statements, use a full anonymous function (`function`).
- Use the model's `id` property instead of calling `getKey()`.
- When querying from an Eloquent model and the required relationships are already defined, prefer `kirschbaum-development/eloquent-power-joins` methods such as `joinRelationship()` and `leftJoinRelationship()` over manual `join()` or `leftJoin()` calls. Use manual joins only when no suitable model relationship exists.
- When using `eloquent-power-joins` methods such as `joinRelationship()` or `leftJoinRelationship()`, always assign an explicit alias to every joined relationship with `PowerJoinClause::as()` and reference that alias in the rest of the query.
- `Date::use(CarbonImmutable::class)` is enabled by default; Laravel date helpers return immutable Carbon instances unless code overrides this locally.

## Testing Guidance

- Write tests only when explicitly asked or when the requested change is test-focused.
- For domain behavior, invoke the underlying actions or services directly.
- Run tests when explicitly asked or when you add/modify tests.
- Always rely on the environment from `phpunit.xml` for test runs.
- The test environment uses `APP_LOCALE=ru`, SQLite `:memory:`, sync queues, array cache/session/mail, and disabled Telescope.
- For narrow changes, prefer the smallest relevant Pest test command before broader suites.
