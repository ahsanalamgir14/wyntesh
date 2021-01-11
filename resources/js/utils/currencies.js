import store from '@/store';

/**
 * @param {Array} value
 * @returns {Boolean}
 * @example see @/views/permission/Directive.vue
 */
export function currency_convert(value) {
  let currency=store.getters.currency;
  value=parseFloat(value);
  return  parseFloat(parseFloat(value/currency.conversion_rate).toFixed(2));
}

export function reverse_currency_convert(value) {
  let currency=store.getters.currency;
  value=parseFloat(value);
  return  parseFloat(value*currency.conversion_rate).toFixed(2);
}

export function currency_symbol() {
  let currency=store.getters.currency;
  return currency.symbol;
}

export function convert(currency,value) {
  value=parseFloat(value);
  return parseFloat(value/currency.conversion_rate).toFixed(2);
}

export function convert_with_symbol(value) {
  let currency=store.getters.currency;
  let converted=parseFloat(value/currency.conversion_rate).toFixed(2);
  return currency.symbol+' '+converted;
}


export function parse_currency(value,base_currency = "INR")  {
    value = parseFloat(value);
    if(base_currency === "INR"){
        var currency =  value.toLocaleString('en-IN', {
            maximumFractionDigits: 2,    
            style: 'currency',
            currency: 'INR'
        });
        return currency;
    }else{
        var currency = number.toLocaleString('en-US', {
            maximumFractionDigits: 2,    
            style: 'currency',
            currency: 'USD'
        });
        return currency;
    }
}

