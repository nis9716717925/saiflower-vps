import prisma from '../config/database';
import { NotFoundError } from '../utils/errors';

export class AddressService {
  async list(userId: string) {
    return prisma.address.findMany({
      where: { userId },
      orderBy: [{ isDefault: 'desc' }, { createdAt: 'desc' }],
    });
  }

  async getById(userId: string, addressId: string) {
    const address = await prisma.address.findFirst({
      where: { id: addressId, userId },
    });
    if (!address) throw new NotFoundError('Address not found');
    return address;
  }

  async create(
    userId: string,
    data: {
      label?: string;
      fullName: string;
      phone: string;
      addressLine1: string;
      addressLine2?: string;
      city: string;
      state: string;
      postalCode: string;
      country?: string;
      isDefault?: boolean;
    },
  ) {
    if (data.isDefault) {
      await prisma.address.updateMany({
        where: { userId },
        data: { isDefault: false },
      });
    }

    const count = await prisma.address.count({ where: { userId } });
    return prisma.address.create({
      data: {
        userId,
        ...data,
        isDefault: data.isDefault ?? count === 0,
      },
    });
  }

  async update(
    userId: string,
    addressId: string,
    data: Partial<{
      label: string;
      fullName: string;
      phone: string;
      addressLine1: string;
      addressLine2: string;
      city: string;
      state: string;
      postalCode: string;
      country: string;
      isDefault: boolean;
    }>,
  ) {
    await this.getById(userId, addressId);

    if (data.isDefault) {
      await prisma.address.updateMany({
        where: { userId },
        data: { isDefault: false },
      });
    }

    return prisma.address.update({
      where: { id: addressId },
      data,
    });
  }

  async delete(userId: string, addressId: string) {
    const address = await this.getById(userId, addressId);
    await prisma.address.delete({ where: { id: addressId } });

    if (address.isDefault) {
      const next = await prisma.address.findFirst({
        where: { userId },
        orderBy: { createdAt: 'desc' },
      });
      if (next) {
        await prisma.address.update({
          where: { id: next.id },
          data: { isDefault: true },
        });
      }
    }

    return { message: 'Address deleted' };
  }

  async setDefault(userId: string, addressId: string) {
    await this.getById(userId, addressId);
    await prisma.address.updateMany({
      where: { userId },
      data: { isDefault: false },
    });
    return prisma.address.update({
      where: { id: addressId },
      data: { isDefault: true },
    });
  }
}

export const addressService = new AddressService();
