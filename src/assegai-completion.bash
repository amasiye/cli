#!/usr/bin/env bash

_assegai_completion()
{
  local cur prev line opts database_types
  COMPREPLY=()
  cur="${COMP_WORDS[COMP_CWORD]}"
  prev="${COMP_WORDS[COMP_CWORD-1]}"
  prev_prev="${COMP_WORDS[COMP_CWORD-2]}"
  line="${COMP_LINE}"
  opts=""
  database_types="mysql pgsql sqlite mongo"

  case "$prev_prev" in
    database)
    case "$prev" in
      list|reset)
      return
      ;;

      **)
      ;;
    esac
    ;;

    g|generate)
    case "$prev" in    
      application|attribute|config|controller|class|guard|interceptor|interface|middleware|module|pipe|resource|service|test)
      return
      ;;

      **)
      ;;
    esac
    ;;

    migration)
    case "$prev" in
      list|redo|revert|run)
      COMPREPLY=($(compgen -W "$database_types" -- $cur))
      return
      ;;

      **)
      ;;
    esac
    ;;

    **)
    ;;
  esac

  case "$prev" in
    mysql|pgsql|sqlite)
    return
    ;;

    add|help|info|lint|new|remove|script|serve|test|update|version)
    return
    ;;

    completion)
    COMPREPLY=($(compgen -W "script" -- "$cur"))
    ;;

    database)
    COMPREPLY=($(compgen -W "list reset setup sync" -- "$cur"))
    ;;

    generate)
    if [[ "$prev_prev" == "migration" ]] ; then
      COMPREPLY=($(compgen -W "$database_types" -- $cur))
    else
      COMPREPLY=($(compgen -W "application class controller entity guard module repository resource service test" -- "$cur"))
    fi
    ;;

    migration)
    COMPREPLY=($(compgen -W "generate list redo revert run" -- "$cur"))
    ;;

    **)
    COMPREPLY=($(compgen -W "add completion generate help info list new remove serve schema test update version" -- "$cur"))
    ;;
  esac
}

complete -F _assegai_completion assegai
