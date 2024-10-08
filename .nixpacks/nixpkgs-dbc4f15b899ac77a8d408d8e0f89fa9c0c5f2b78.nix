{ }:

let pkgs = import (fetchTarball "https://github.com/NixOS/nixpkgs/archive/dbc4f15b899ac77a8d408d8e0f89fa9c0c5f2b78.tar.gz") { overlays = [ (import (builtins.fetchTarball "https://github.com/railwayapp/nix-npm-overlay/archive/main.tar.gz")) ]; };
in with pkgs;
  let
    APPEND_LIBRARY_PATH = "${lib.makeLibraryPath [ libmysqlclient ] }";
    myLibraries = writeText "libraries" ''
      export LD_LIBRARY_PATH="${APPEND_LIBRARY_PATH}:$LD_LIBRARY_PATH"
      
    '';
  in
    buildEnv {
      ignoreCollisions = true;
      name = "dbc4f15b899ac77a8d408d8e0f89fa9c0c5f2b78-env";
      paths = [
        (runCommand "dbc4f15b899ac77a8d408d8e0f89fa9c0c5f2b78-env" { } ''
          mkdir -p $out/etc/profile.d
          cp ${myLibraries} $out/etc/profile.d/dbc4f15b899ac77a8d408d8e0f89fa9c0c5f2b78-env.sh
        '')
        (php83.withExtensions (pe: pe.enabled ++ [])) libmysqlclient nginx nodejs_18 php83Packages.composer pnpm-9_x
      ];
    }
