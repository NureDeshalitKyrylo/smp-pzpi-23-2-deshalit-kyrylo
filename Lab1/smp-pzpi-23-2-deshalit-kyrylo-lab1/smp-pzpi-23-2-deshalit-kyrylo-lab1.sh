#!/usr/bin/env bash

VERSION="1.0"
SCRIPT_NAME="pzpi-23-2-deshalit-kyrylo-task2"
QUIET_MODE=false



show_help() {
    cat << EOF
Використання: $SCRIPT_NAME [--help | --version] | [[-q|--quiet] [академ_група] файл_із_cist.csv]

$SCRIPT_NAME 'ПЗПІ-23-12' TimeTable_15_03_2025.csv
    $SCRIPT_NAME --help
    $SCRIPT_NAME --version
    $SCRIPT_NAME -q 'ПЗПІ-23-2' TimeTable_01_02_2025.csv

EOF
}

show_version() {
    echo "$SCRIPT_NAME версія $VERSION"
}

error_exit() {
    echo "Код помилки: $1" >&2
    echo "ПОМИЛКА: $2" >&2
    exit 0
}

log_info() {
    if [[ "$QUIET_MODE" != true ]]; then
        echo "$1"
    fi
}

select_file() {
    local files=($(ls TimeTable_??_??_20??.csv 2>/dev/null | sort -t_ -k3n -k2n -k4n))
    
    if [[ ${#files[@]} -eq 0 ]]; then
        error_exit 1 "Не знайдено жодного файлу за шаблоном TimeTable_??_??_20??.csv"
    fi
    
    echo "Виберіть файл розкладу:" > /dev/tty

    select file in "${files[@]}"; do
        if [[ -n "$file" ]]; then
            echo "$file"
            return 0
        else
            echo "Невірний вибір. Спробуйте ще раз." >&2
        fi
    done
}

get_groups_from_file() {
    local file="$1" 

    if [[ ! -f "$file" ]]; then
        error_exit 2 "Файл '$file' не знайдено"
    fi

    if [[ ! -r "$file" ]]; then
        error_exit 3 "Файл '$file' недоступний для читання"
    fi

   
   gawk -v FPAT='([^,]+)|"([^"]*)"' '
    NR != 1 && $1 ~ / - / {
        gsub(/"/, "", $1)
        split($1, g, " - ")
        print g[1]
    }
    ' < <(cat "$file" | sed 's/\r/\r\n/g' | iconv -f cp1251 -t utf-8) | sort -u
}

select_group() {
    local file="$1"
    local groups_array
    
    
    mapfile -t groups_array < <(get_groups_from_file "$file")
    
    if [[ ${#groups_array[@]} -eq 0 ]]; then
            gawk -v FPAT='([^,]+)|"([^"]*)"' '
             NR == 2{
             gsub(/"/, "", $1)
             split($1, g, " ")
             print g[4]
         }
         ' < <(cat "$file" | sed 's/\r/\r\n/g' | iconv -f cp1251 -t utf-8)
        return 0;
    fi
    
    echo "Виберіть академічну групу:" > /dev/tty
    select group in "${groups_array[@]}"; do
        if [[ -n "$group" ]]; then
            echo "$group"
            return 0
        else
            echo "Невірний вибір. Спробуйте ще раз." >&2
        fi
    done
}




process_csv() {
    local group="$1"
    local input_file="$2"
    local output_file="$3"
    
    local groups_array

    mapfile -t groups_array < <(get_groups_from_file "$input_file")


    $(printf '%s\n' "${groups_array[@]}" | grep -Fqx "$group")

    if [[ $? -ne 0 ]]; then 
        echo "Група '$group' не знайдена у файлі '$input_file'" >&2

        group=$(select_group "$input_file")
    fi
    
    log_info "Обробка розкладу для групи: $group"
    log_info "Вхідний файл: $input_file"
    log_info "Вихідний файл: $output_file"
    
   
local csv_header="Subject,Start Date,Start Time,End Date,End Time,Description"
local ostream="/dev/stdout"

if [ "$QUIET_MODE" == true ]; then
    ostream="/dev/null"
    shift
fi

    cat "$input_file" | sed 's/\r/\n/g' | 
iconv -f cp1251 -t utf-8 "$input_file" | sed 's/\r/\n/g' \
| awk -v FPAT='[^,]*|"[^"]*"' \
      -v match_pattern="$([ -n "$group" ] && echo "^\"$group - ")" \
      -v header_line="$csv_header" '

BEGIN {
    print header_line
}

function adjust_time(t) {
    gsub(/:|"/, " ", t)
    return strftime("%I:%M %p", mktime("1970 01 01" t))
}

function adjust_date(d) {
    gsub(/"/, "", d)
    split(d, parts, ".")
    return strftime("%m/%d/%Y", mktime(parts[3] " " parts[2] " " parts[1] " 00 00 00"))
}

NR > 1 && $1 ~ match_pattern {
    split($1, parts, " - ")
    event_title=parts[2];
          
    split(event_title, words, " ")      

    if(labCounter[$2] % 2 == 0) {
        counter[$2]++
    }

    if(words[2] == "Лб" || words[2] == "лб") {
        labCounter[$2]++;   
    }

         
    
        
    from_date = adjust_date($2)
    from_time = adjust_time($3)
    to_date_ = adjust_date($4)
    to_time_ = adjust_time($5)
    details = $12

    print "\"" event_title "; №" counter[$2] "\"," from_date "," from_time "," to_date_ "," to_time_ "," details
}
' | tee "$output_file" > "$ostream"

    }

main() {
    local group=""
    local input_file=""
    
    while [[ $# -gt 0 ]]; do
        case $1 in
            --help)
                show_help
                exit 0
                ;;
            --version)
                show_version
                exit 0
                ;;
            -q|--quiet)
                QUIET_MODE=true
                shift
                ;;
            *)
                if [[ -z "$group" ]]; then
                    group="$1"
                elif [[ -z "$input_file" ]]; then
                    input_file="$1"
                else
                    error_exit 7 "Занадто багато аргументів"
                fi
                shift
                ;;
        esac
    done

   
    
    if [[ -z "$input_file" ]]; then
        input_file=$(select_file)
    fi
    
    if [[ ! -f "$input_file" ]]; then
        error_exit 2 "Файл '$input_file' не знайдено"
        input_file=$(select_file)
    fi
    
    if [[ ! -r "$input_file" ]]; then
        error_exit 3 "Файл '$input_file' недоступний для читання"
    fi
    
    if [[ -z "$group" ]]; then
        group=$(select_group "$input_file")
    fi
    
    local base_name=$(basename "$input_file" .csv)
    local date_part=$(echo "$base_name" | sed 's/TimeTable_//')
    local output_file="Google_TimeTable_${date_part}.csv"
    
    process_csv "$group" "$input_file" "$output_file"
}

main "$@"