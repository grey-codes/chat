workflow "PHP Linting" {
  resolves = ["Execute"]
  on = "push"
}

action "Execute" {
  uses = "awanesia/PHP-Lint@master"
}
