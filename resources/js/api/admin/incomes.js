import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/admin/incomes',
    method: 'get',
    params: query
  })
}

export function fetchIncome(id) {
  return request({
    url: '/admin/income/'+id,
    method: 'get'
  })
}

export function deleteIncome(id) {
  return request({
    url: '/admin/income/'+id+'/delete',
    method: 'delete'
  })
}


export function createIncome(data) {
  return request({
    url: '/admin/income',
    method: 'post',
    data
  })
}

export function updateIncome(data) {
  return request({
    url: '/admin/income/update',
    method: 'post',
    data
  })
}

export function fetchListIncomeParameter(query) {
  return request({
    url: '/admin/income-parameters',
    method: 'get',
    params: query
  })
}

export function fetchIncomeParameter(id) {
  return request({
    url: '/admin/income-parameter/'+id,
    method: 'get'
  })
}

export function deleteIncomeParameter(id) {
  return request({
    url: '/admin/income-parameter/'+id+'/delete',
    method: 'delete'
  })
}


export function createIncomeParameter(data) {
  return request({
    url: '/admin/income-parameter',
    method: 'post',
    data
  })
}

export function updateIncomeParameter(data) {
  return request({
    url: '/admin/income-parameter/update',
    method: 'post',
    data
  })
}

