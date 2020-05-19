import request from '@/utils/request'

export function getSettings() {
  return request({
    url: '/admin/settings',
    method: 'get'
  })
}

export function getCompanySettings() {
  return request({
    url: '/admin/settings/company',
    method: 'get'
  })
}

export function saveCompanySettings(data) {
  return request({
    url: '/admin/settings',
    method: 'post',
    data
  })
}
