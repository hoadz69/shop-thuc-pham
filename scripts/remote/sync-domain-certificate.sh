#!/usr/bin/env bash
set -Eeuo pipefail

domain="thucphamthuytrang.site"
lineage="${RENEWED_LINEAGE:-/etc/letsencrypt/live/${domain}}"
cert_dir="/www/server/panel/vhost/cert/103.77.240.28"
backup_root="/www/backup/site/thuc-pham-thuy-trang/cert-deploy"
nginx_bin="/www/server/nginx/sbin/nginx"

if [[ "${lineage}" != "/etc/letsencrypt/live/${domain}" ]]; then
    printf 'Unexpected certificate lineage: %s\n' "${lineage}" >&2
    exit 10
fi

for source_file in fullchain.pem privkey.pem; do
    if [[ ! -s "${lineage}/${source_file}" ]]; then
        printf 'Missing certificate artifact: %s\n' "${source_file}" >&2
        exit 11
    fi
done

cert_public_key=$(openssl x509 -in "${lineage}/fullchain.pem" -pubkey -noout \
    | openssl pkey -pubin -outform DER 2>/dev/null \
    | sha256sum | awk '{print $1}')
private_public_key=$(openssl pkey -in "${lineage}/privkey.pem" -pubout -outform DER 2>/dev/null \
    | sha256sum | awk '{print $1}')

if [[ -z "${cert_public_key}" || "${cert_public_key}" != "${private_public_key}" ]]; then
    printf 'Certificate and private key do not match.\n' >&2
    exit 12
fi

openssl x509 -in "${lineage}/fullchain.pem" -noout -checkhost "${domain}" >/dev/null
openssl x509 -in "${lineage}/fullchain.pem" -noout -checkhost "www.${domain}" >/dev/null

stamp=$(date -u +%Y%m%dT%H%M%SZ)
backup_dir="${backup_root}/${stamp}"
mkdir -p "${backup_dir}" "${cert_dir}"
chmod 700 "${backup_root}" "${backup_dir}" "${cert_dir}"

for current_file in fullchain.pem privkey.pem; do
    if [[ -f "${cert_dir}/${current_file}" ]]; then
        cp -a -- "${cert_dir}/${current_file}" "${backup_dir}/${current_file}"
    fi
done

install -o root -g root -m 600 "${lineage}/fullchain.pem" "${cert_dir}/fullchain.pem.new"
install -o root -g root -m 600 "${lineage}/privkey.pem" "${cert_dir}/privkey.pem.new"
mv -f -- "${cert_dir}/fullchain.pem.new" "${cert_dir}/fullchain.pem"
mv -f -- "${cert_dir}/privkey.pem.new" "${cert_dir}/privkey.pem"

if ! "${nginx_bin}" -t; then
    cp -a -- "${backup_dir}/fullchain.pem" "${cert_dir}/fullchain.pem"
    cp -a -- "${backup_dir}/privkey.pem" "${cert_dir}/privkey.pem"
    "${nginx_bin}" -t
    printf 'Nginx rejected the renewed certificate; previous files restored.\n' >&2
    exit 13
fi

"${nginx_bin}" -s reload
printf 'Certificate deployed for %s; rollback=%s\n' "${domain}" "${backup_dir}"
