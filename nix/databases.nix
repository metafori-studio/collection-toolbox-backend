{
  pkgs,
  config,
}:
let
  pkgs_postgresql = pkgs.postgresql_18;
  pkgs_valkey = pkgs.valkey;
in
{
  inherit pkgs_postgresql pkgs_valkey;
}
