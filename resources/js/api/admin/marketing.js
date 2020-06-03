import request from '@/utils/request'


export function sendMassEmail(data) {
  return request({
    url: '/admin/marketing/email/mass',
    method: 'post',
    data
  })
}

export function sendMassSMS(data) {
  return request({
    url: '/admin/marketing/sms/mass',
    method: 'post',
    data
  })
}

