import request from '@/utils/request'

export function getCompanySettings() {
  return request({
    url: '/admin/company-settings',
    method: 'get'
  })
}

export function saveCompanySettings(data) {
  return request({
    url: '/admin/company-settings',
    method: 'post',
    data
  })
}
