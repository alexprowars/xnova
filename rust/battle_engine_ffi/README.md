Rust battle engine imported from OGameX PR #1558 and adapted for Xnova.

PHP calls it through `App\Engine\Battle\NativeEngine`. Every result returned by
`fight_battle_rounds` must be released exactly once using `free_battle_result`.

See [the workspace README](../README.md) for licensing, builds and benchmarks.
