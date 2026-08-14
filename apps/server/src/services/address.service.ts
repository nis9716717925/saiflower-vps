import { prisma } from '../db/client';
import { NotFoundError, ValidationError } from '../utils/errors';

export type AddressType = 'Home' | 'Work' | 'Other';

export interface AddressInput {
  recipientName: string;
  mobile: string;
  email?: string | null;
  flatHouseNo: string;
  apartmentStreetLocality: string;
  pincode: string;
  addressType?: AddressType;
  isDefault?: boolean;
}

function normalizeMobile(mobile: string): string {
  return mobile.replace(/\D/g, '');
}

function validateAddressInput(input: AddressInput) {
  const recipientName = (input.recipientName ?? '').trim();
  const mobile = normalizeMobile(input.mobile ?? '');
  const email = (input.email ?? '').trim();
  const flatHouseNo = (input.flatHouseNo ?? '').trim();
  const apartmentStreetLocality = (input.apartmentStreetLocality ?? '').trim();
  const pincode = (input.pincode ?? '').trim();
  const addressType = (input.addressType ?? 'Home') as AddressType;

  const errors: Record<string, string[]> = {};
  if (!recipientName) errors.recipientName = ['Recipient name is required'];
  if (!/^[6-9]\d{9}$/.test(mobile)) {
    errors.mobile = ['Enter a valid 10-digit Indian mobile number'];
  }
  if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    errors.email = ['Enter a valid email address'];
  }
  if (!flatHouseNo) errors.flatHouseNo = ['Flat/House no. is required'];
  if (!apartmentStreetLocality) {
    errors.apartmentStreetLocality = ['Apartment / Street / Locality is required'];
  }
  if (!/^\d{6}$/.test(pincode)) errors.pincode = ['Enter a valid 6-digit pincode'];
  if (!['Home', 'Work', 'Other'].includes(addressType)) {
    errors.addressType = ['Address type must be Home, Work, or Other'];
  }

  if (Object.keys(errors).length) {
    throw new ValidationError('Please fix the address details', errors);
  }

  return {
    recipientName,
    mobile,
    email: email || null,
    flatHouseNo,
    apartmentStreetLocality,
    pincode,
    addressType,
  };
}

function mapAddress(row: {
  id: number;
  customerId: number;
  recipientName: string;
  mobile: string;
  email: string | null;
  flatHouseNo: string;
  apartmentStreetLocality: string;
  pincode: string;
  addressType: AddressType;
  isDefault: number;
  createdAt: Date | null;
  updatedAt: Date | null;
}) {
  return {
    id: row.id,
    customerId: row.customerId,
    recipientName: row.recipientName,
    mobile: row.mobile,
    email: row.email,
    flatHouseNo: row.flatHouseNo,
    apartmentStreetLocality: row.apartmentStreetLocality,
    pincode: row.pincode,
    addressType: row.addressType,
    isDefault: Boolean(row.isDefault),
    createdAt: row.createdAt,
    updatedAt: row.updatedAt,
    addressLine: `${row.flatHouseNo}, ${row.apartmentStreetLocality}`,
    city: 'Delhi',
    formattedAddress: `${row.flatHouseNo}, ${row.apartmentStreetLocality}, ${row.pincode}, India`,
  };
}

export async function listAddresses(customerId: number) {
  const rows = await prisma.customerAddress.findMany({
    where: { customerId },
    orderBy: [{ isDefault: 'desc' }, { id: 'desc' }],
  });
  return rows.map(mapAddress);
}

export async function getAddress(customerId: number, addressId: number) {
  const row = await prisma.customerAddress.findFirst({
    where: { id: addressId, customerId },
  });
  if (!row) throw new NotFoundError('Address not found');
  return mapAddress(row);
}

export async function createAddress(customerId: number, input: AddressInput) {
  const data = validateAddressInput(input);
  const existingCount = await prisma.customerAddress.count({ where: { customerId } });
  const makeDefault = Boolean(input.isDefault) || existingCount === 0;

  if (makeDefault) {
    await prisma.customerAddress.updateMany({
      where: { customerId },
      data: { isDefault: 0 },
    });
  }

  const row = await prisma.customerAddress.create({
    data: {
      customerId,
      ...data,
      isDefault: makeDefault ? 1 : 0,
    },
  });

  // Keep legacy profile fields in sync for convenience
  await prisma.customers.update({
    where: { id: customerId },
    data: {
      phone: data.mobile,
      address: `${data.flatHouseNo}, ${data.apartmentStreetLocality}`,
      city: 'Delhi',
      pincode: data.pincode,
    },
  });

  return mapAddress(row);
}

export async function updateAddress(
  customerId: number,
  addressId: number,
  input: AddressInput,
) {
  const existing = await prisma.customerAddress.findFirst({
    where: { id: addressId, customerId },
  });
  if (!existing) throw new NotFoundError('Address not found');

  const data = validateAddressInput(input);
  const makeDefault = input.isDefault === true || Boolean(existing.isDefault);

  if (makeDefault) {
    await prisma.customerAddress.updateMany({
      where: { customerId },
      data: { isDefault: 0 },
    });
  }

  const row = await prisma.customerAddress.update({
    where: { id: addressId },
    data: {
      ...data,
      isDefault: makeDefault ? 1 : 0,
    },
  });

  if (makeDefault) {
    await prisma.customers.update({
      where: { id: customerId },
      data: {
        phone: data.mobile,
        address: `${data.flatHouseNo}, ${data.apartmentStreetLocality}`,
        city: 'Delhi',
        pincode: data.pincode,
      },
    });
  }

  return mapAddress(row);
}

export async function setDefaultAddress(customerId: number, addressId: number) {
  const existing = await prisma.customerAddress.findFirst({
    where: { id: addressId, customerId },
  });
  if (!existing) throw new NotFoundError('Address not found');

  await prisma.customerAddress.updateMany({
    where: { customerId },
    data: { isDefault: 0 },
  });

  const row = await prisma.customerAddress.update({
    where: { id: addressId },
    data: { isDefault: 1 },
  });

  await prisma.customers.update({
    where: { id: customerId },
    data: {
      phone: row.mobile,
      address: `${row.flatHouseNo}, ${row.apartmentStreetLocality}`,
      city: 'Delhi',
      pincode: row.pincode,
    },
  });

  return mapAddress(row);
}

export async function deleteAddress(customerId: number, addressId: number) {
  const existing = await prisma.customerAddress.findFirst({
    where: { id: addressId, customerId },
  });
  if (!existing) throw new NotFoundError('Address not found');

  await prisma.customerAddress.delete({ where: { id: addressId } });

  if (existing.isDefault) {
    const next = await prisma.customerAddress.findFirst({
      where: { customerId },
      orderBy: { id: 'desc' },
    });
    if (next) {
      await prisma.customerAddress.update({
        where: { id: next.id },
        data: { isDefault: 1 },
      });
    }
  }

  return { deleted: true };
}
