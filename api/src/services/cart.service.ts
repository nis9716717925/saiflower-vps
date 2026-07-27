import { v4 as uuidv4 } from 'uuid';
import prisma from '../config/database';
import { BadRequestError, NotFoundError } from '../utils/errors';
import { decimalToNumber } from '../utils/sanitize';

function formatCartItem(item: Record<string, unknown>) {
  const variant = item.variant as Record<string, unknown> | null;
  const product = item.product as Record<string, unknown>;
  const price = variant
    ? decimalToNumber(variant.price as never)
    : decimalToNumber(product.basePrice as never);

  return {
    id: item.id,
    productId: item.productId,
    variantId: item.variantId,
    quantity: item.quantity,
    unitPrice: price,
    totalPrice: price * (item.quantity as number),
    product: {
      id: product.id,
      name: product.name,
      slug: product.slug,
      images: product.images,
    },
    variant: variant
      ? { id: variant.id, name: variant.name, sku: variant.sku, stock: variant.stock }
      : null,
  };
}

export class CartService {
  async getOrCreateCart(userId?: string, guestId?: string) {
    if (userId) {
      let cart = await prisma.cart.findUnique({
        where: { userId },
        include: this.cartInclude(),
      });
      if (!cart) {
        cart = await prisma.cart.create({
          data: { userId },
          include: this.cartInclude(),
        });
      }
      return this.formatCart(cart);
    }

    if (!guestId) {
      guestId = uuidv4();
    }

    let cart = await prisma.cart.findUnique({
      where: { guestId },
      include: this.cartInclude(),
    });
    if (!cart) {
      cart = await prisma.cart.create({
        data: { guestId },
        include: this.cartInclude(),
      });
    }

    return { ...this.formatCart(cart), guestId };
  }

  async mergeGuestCart(userId: string, guestId: string) {
    const [userCart, guestCart] = await Promise.all([
      prisma.cart.findUnique({ where: { userId }, include: { items: true } }),
      prisma.cart.findUnique({ where: { guestId }, include: { items: true } }),
    ]);

    if (!guestCart?.items.length) return this.getOrCreateCart(userId);

    let cart = userCart;
    if (!cart) {
      cart = await prisma.cart.create({ data: { userId }, include: { items: true } });
    }

    for (const guestItem of guestCart.items) {
      const existing = cart.items.find(
        (i) => i.productId === guestItem.productId && i.variantId === guestItem.variantId,
      );
      if (existing) {
        await prisma.cartItem.update({
          where: { id: existing.id },
          data: { quantity: existing.quantity + guestItem.quantity },
        });
      } else {
        await prisma.cartItem.create({
          data: {
            cartId: cart.id,
            productId: guestItem.productId,
            variantId: guestItem.variantId,
            quantity: guestItem.quantity,
          },
        });
      }
    }

    await prisma.cart.delete({ where: { id: guestCart.id } });
    return this.getOrCreateCart(userId);
  }

  async addItem(
    cartId: string,
    productId: string,
    quantity: number,
    variantId?: string,
  ) {
    const product = await prisma.product.findUnique({
      where: { id: productId, isActive: true },
      include: { variants: true },
    });
    if (!product) throw new NotFoundError('Product not found');

    if (variantId) {
      const variant = product.variants.find((v) => v.id === variantId && v.isActive);
      if (!variant) throw new NotFoundError('Variant not found');
      if (variant.stock < quantity) throw new BadRequestError('Insufficient stock');
    }

    const existing = await prisma.cartItem.findFirst({
      where: { cartId, productId, variantId: variantId ?? null },
    });

    if (existing) {
      await prisma.cartItem.update({
        where: { id: existing.id },
        data: { quantity: existing.quantity + quantity },
      });
    } else {
      await prisma.cartItem.create({
        data: { cartId, productId, variantId, quantity },
      });
    }

    const cart = await prisma.cart.findUnique({
      where: { id: cartId },
      include: this.cartInclude(),
    });
    return this.formatCart(cart!);
  }

  async updateItem(cartItemId: string, quantity: number, cartId: string) {
    if (quantity < 1) throw new BadRequestError('Quantity must be at least 1');

    const item = await prisma.cartItem.findFirst({
      where: { id: cartItemId, cartId },
      include: { variant: true },
    });
    if (!item) throw new NotFoundError('Cart item not found');

    if (item.variant && item.variant.stock < quantity) {
      throw new BadRequestError('Insufficient stock');
    }

    await prisma.cartItem.update({
      where: { id: cartItemId },
      data: { quantity },
    });

    const cart = await prisma.cart.findUnique({
      where: { id: cartId },
      include: this.cartInclude(),
    });
    return this.formatCart(cart!);
  }

  async removeItem(cartItemId: string, cartId: string) {
    const item = await prisma.cartItem.findFirst({
      where: { id: cartItemId, cartId },
    });
    if (!item) throw new NotFoundError('Cart item not found');

    await prisma.cartItem.delete({ where: { id: cartItemId } });

    const cart = await prisma.cart.findUnique({
      where: { id: cartId },
      include: this.cartInclude(),
    });
    return this.formatCart(cart!);
  }

  async clearCart(cartId: string) {
    await prisma.cartItem.deleteMany({ where: { cartId } });
    const cart = await prisma.cart.findUnique({
      where: { id: cartId },
      include: this.cartInclude(),
    });
    return this.formatCart(cart!);
  }

  private cartInclude() {
    return {
      items: {
        include: {
          product: { include: { images: { orderBy: { sortOrder: 'asc' as const }, take: 1 } } },
          variant: true,
        },
      },
    };
  }

  private formatCart(cart: Record<string, unknown>) {
    const items = (cart.items as Record<string, unknown>[]).map(formatCartItem);
    const subtotal = items.reduce((sum, i) => sum + i.totalPrice, 0);
    return {
      id: cart.id,
      userId: cart.userId,
      guestId: cart.guestId,
      items,
      itemCount: items.reduce((sum, i) => sum + (i.quantity as number), 0),
      subtotal,
    };
  }
}

export const cartService = new CartService();
