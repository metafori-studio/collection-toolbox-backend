{
  pkgs,
  config,
}:
let
  pkgs_seaweedfs = pkgs.seaweedfs;
in
{
  inherit pkgs_seaweedfs;
}
