import { apiSend } from '@/lib/api';
import type { Product } from '@/lib/types';

type BuyProduct = Pick<Product, 'id' | 'type' | 'name' | 'price' | 'image'>;

export async function addProductToCart(product: BuyProduct, quantity = 1): Promise<void> {
  await apiSend('/cart/items', 'POST', {
    productId: product.id,
    category: product.type,
    quantity,
    name: product.name,
    price: product.price,
    image: product.image,
  });
}
